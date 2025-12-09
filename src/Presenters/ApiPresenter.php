<?php

namespace Crm\ApiModule\Presenters;

use Crm\ApiModule\Models\Api\ApiHandler;
use Crm\ApiModule\Models\Api\ApiHandlerInterface;
use Crm\ApiModule\Models\Api\ApiHeadersConfig;
use Crm\ApiModule\Models\Api\ApiLoggerConfig;
use Crm\ApiModule\Models\Api\Runner;
use Crm\ApiModule\Models\Authorization\ApiAuthorizationInterface;
use Crm\ApiModule\Models\Authorization\BearerTokenAuthorization;
use Crm\ApiModule\Models\Authorization\TokenParser;
use Crm\ApiModule\Models\Router\ApiIdentifier;
use Crm\ApiModule\Models\Router\ApiRoutesContainer;
use Crm\ApiModule\Repositories\ApiTokenStatsRepository;
use Crm\ApplicationModule\Hermes\HermesMessage;
use Crm\ApplicationModule\Hermes\LogRedact;
use Crm\ApplicationModule\Models\Config\ApplicationConfig;
use Crm\UsersModule\Models\Auth\UserTokenAuthorization;
use Nette\Application\IPresenter;
use Nette\Application\Request;
use Nette\Application\Response;
use Nette\Http\Request as HttpRequest;
use Nette\Http\Response as HttpResponse;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Tomaj\Hermes\Emitter;
use Tomaj\NetteApi\Response\JsonApiResponse;
use Tomaj\NetteApi\Response\ResponseInterface;
use Tracy\Debugger;

class ApiPresenter implements IPresenter
{
    public function __construct(
        private ApplicationConfig $applicationConfig,
        private ApiRoutesContainer $apiRoutesContainer,
        private ApiTokenStatsRepository $apiTokenStatsRepository,
        private ApiHeadersConfig $apiHeadersConfig,
        private ApiLoggerConfig $apiLoggerConfig,
        private Emitter $hermesEmitter,
        private Runner $apiRunner,
        private HttpRequest $httpRequest,
        private HttpResponse $httpResponse,
    ) {
    }

    public function run(Request $request): Response
    {
        Debugger::timer();

        $origin = $_SERVER['HTTP_ORIGIN'] ?? null;
        if (!$this->apiHeadersConfig->isOriginAllowed($origin)) {
            $response = new JsonApiResponse(HttpResponse::S403_FORBIDDEN, [
                'error' => 'origin_not_allowed',
                'message' => 'Origin is not allowed: ' . $origin,
            ]);
            $this->httpResponse->setCode(HttpResponse::S403_FORBIDDEN);
            return $response;
        }
        if ($origin) {
            $this->httpResponse->addHeader('Access-Control-Allow-Origin', $origin);
        }

        if ($this->apiHeadersConfig->hasAllowedCredentialsHeader()) {
            $this->httpResponse->addHeader(
                'Access-Control-Allow-Credentials',
                'true',
            );
        }

        // handle preflight request
        if ($this->httpRequest->isMethod('OPTIONS')) {
            $this->httpResponse->addHeader(
                'Access-Control-Allow-Headers',
                $this->apiHeadersConfig->getAllowedHeaders(),
            );

            $this->httpResponse->addHeader(
                'Access-Control-Allow-Methods',
                $this->apiHeadersConfig->getAllowedHttpMethods(),
            );

            if ($this->apiHeadersConfig->getAccessControlMaxAge() !== null) {
                $this->httpResponse->addHeader(
                    'Access-Control-Max-Age',
                    $this->apiHeadersConfig->getAccessControlMaxAge(),
                );
            }

            return new JsonApiResponse(HttpResponse::S200_OK, ['options' => 'ok']);
        }

        $method = $request->getMethod();
        $version = $request->getParameter('version');
        $category = $request->getParameter('package');
        $action = $request->getParameter('apiAction');

        if (!isset($version, $category, $action)) {
            $response = new JsonApiResponse(HttpResponse::S404_NOT_FOUND, [
                'error' => sprintf('Unknown api call: version [%s], category [%s], action [%s]', $version, $category, $action),
            ]);
            $this->httpResponse->setCode(HttpResponse::S404_NOT_FOUND);
            return $response;
        }

        $apiIdentifier = new ApiIdentifier($version, $category, $action, $method);
        $handler = $this->apiRoutesContainer->getHandler($apiIdentifier);
        if (!$handler) {
            $response = new JsonApiResponse(HttpResponse::S404_NOT_FOUND, [
                'error' => sprintf('Unknown api call: version [%s], category [%s], action [%s]', $version, $category, $action),
            ]);
            $this->httpResponse->setCode(HttpResponse::S404_NOT_FOUND);
            return $response;
        }

        /** @var ApiAuthorizationInterface $authorization */
        $authorization = $this->apiRoutesContainer->getAuthorization($apiIdentifier);
        if (!$authorization->authorized($handler->resource())) {
            $response = new JsonApiResponse(HttpResponse::S403_FORBIDDEN, [
                'status' => 'error',
                'message' => sprintf('Not authorized: %s', $authorization->getErrorMessage()),
                'error' => 'no_authorization',
            ]);
            $this->httpResponse->setCode(HttpResponse::S403_FORBIDDEN);
            return $response;
        }

        $handler->setAuthorization($authorization);
        $response = $this->apiRunner->run($handler);

        $this->log($apiIdentifier, $authorization, $response, $handler);

        $this->httpResponse->setCode($response->getCode());
        return $response;
    }

    private function log(
        ApiIdentifier $apiIdentifier,
        ApiAuthorizationInterface $authorization,
        ResponseInterface $response,
        ApiHandlerInterface $handler,
    ) {
        $apiLogEnabled = $this->applicationConfig->get('enable_api_log')
            && $this->apiLoggerConfig->isPathEnabled($apiIdentifier);

        $apiStatsEnabled = $this->applicationConfig->get('enable_api_stats');

        $token = '';
        if ($authorization instanceof BearerTokenAuthorization) {
            $tokenParser = new TokenParser();
            $token = $tokenParser->getToken();
            if (!$apiLogEnabled && $apiStatsEnabled) {
                // If we don't log API calls, but do log the API stats, update token stats directly. Otherwise, we'll update it asynchronously.
                $this->apiTokenStatsRepository->updateStats($token);
            }
        }
        // TODO: [users_module] try to refactor this so ApiModule doesn't have dependency on UsersModule
        if ($authorization instanceof UserTokenAuthorization) {
            $tokenParser = new TokenParser();
            $token = $tokenParser->getToken();
        }

        if (!$apiLogEnabled) {
            return;
        }

        $postParams = $this->httpRequest->getPost();
        $queryParams = $this->httpRequest->getUrl()->getQueryParameters();

        $redactedFields = $this->apiLoggerConfig->getRedactedFields();
        $input = [
            'POST' => LogRedact::redactArray($postParams, $redactedFields),
            'GET' => LogRedact::redactArray($queryParams, $redactedFields),
            'RAW' => '',
        ];

        $payload = $this->rawPayload($handler);

        // https://www.php.net/manual/en/reserved.variables.post.php
        $postHandledContentTypes = [
            'application/x-www-form-urlencoded',
            'multipart/form-data',
        ];

        $contentTypeHeader = $this->httpRequest->getHeader('Content-Type');
        $contentType = trim(explode(';', $contentTypeHeader, 2)[0]);

        if ($contentType === 'application/json') {
            try {
                // try to store decoded json (it would be double-encoded due to the following lines)
                $input['JSON'] = LogRedact::redactArray(Json::decode($payload, true), $redactedFields);
            } catch (JsonException $e) {
                // it's not JSON, log the payload as we received it
                $input['RAW'] = $payload;
            }
        } elseif (!in_array($contentType, $postHandledContentTypes, true)) {
            // postHandledContentTypes are already present in $_POST, use RAW for remaining content types
            $input['RAW'] = $payload;
        }

        try {
            $jsonInput = Json::encode($input);
        } catch (JsonException $e) {
            if ($e->getCode() === JSON_ERROR_UTF8) {
                // Occasionally some payloads include "malformed UTF-8" characters which can't be JSON-encoded; this helps.
                // https://stackoverflow.com/a/46305914
                $jsonInput = Json::encode(mb_convert_encoding($input, 'UTF-8', 'UTF-8'));
            } else {
                throw $e;
            }
        }

        $elapsed = Debugger::timer() * 1000;
        $path = $apiIdentifier->getUrl();
        $responseCode = $response->getCode();
        $ipAddress = \Crm\ApplicationModule\Models\Request::getIp();
        $userAgent = \Crm\ApplicationModule\Models\Request::getUserAgent();

        if ($response instanceof JsonApiResponse) {
            $responseBody = Json::encode($response->getPayload());
        } else {
            $responseBody = null;
        }

        $this->hermesEmitter->emit(new HermesMessage('api-log', [
            'token' => $token,
            'path' => $path,
            'jsonInput' => $jsonInput,
            'responseCode' => $responseCode,
            'responseBody' => $responseBody,
            'elapsed' => $elapsed,
            'ipAddress' => $ipAddress,
            'userAgent' => $userAgent,
        ]), HermesMessage::PRIORITY_LOW);
    }

    private function rawPayload(ApiHandlerInterface $handler): string
    {
        if ($handler instanceof ApiHandler) {
            return $handler->rawPayload();
        }

        return file_get_contents('php://input');
    }
}
