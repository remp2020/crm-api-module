<?php

namespace Crm\ApiModule\Presenters;

use Crm\ApiModule\Api\ApiHeadersConfig;
use Crm\ApiModule\Api\ApiParamsValidatorInterface;
use Crm\ApiModule\Api\IdempotentHandlerInterface;
use Crm\ApiModule\Authorization\ApiAuthorizationInterface;
use Crm\ApiModule\Authorization\BearerTokenAuthorization;
use Crm\ApiModule\Authorization\TokenParser;
use Crm\ApiModule\Repository\ApiTokenStatsRepository;
use Crm\ApiModule\Repository\IdempotentKeysRepository;
use Crm\ApiModule\Router\ApiIdentifier;
use Crm\ApiModule\Router\ApiRoutesContainer;
use Crm\ApplicationModule\Config\ApplicationConfig;
use Crm\ApplicationModule\Hermes\HermesMessage;
use Crm\UsersModule\Auth\UserTokenAuthorization;
use Nette\Application\IPresenter;
use Nette\Application\Request;
use Nette\Application\Response;
use Nette\Http\Request as HttpRequest;
use Nette\Http\Response as HttpResponse;
use Tomaj\Hermes\Emitter;
use Tomaj\NetteApi\Params\ParamsProcessor;
use Tomaj\NetteApi\Response\JsonApiResponse;
use Tracy\Debugger;

class ApiPresenter implements IPresenter
{
    private $apiRoutersContainer;

    private $apiTokenStatsRepository;

    private $idempotentKeysRepository;

    private $apiHeadersConfig;

    private $hermesEmitter;

    private $httpRequest;

    private $httpResponse;

    private $applicationConfig;

    public function __construct(
        ApplicationConfig $applicationConfig,
        ApiRoutesContainer $apiRoutesContainer,
        ApiTokenStatsRepository $apiTokenStatsRepository,
        IdempotentKeysRepository $idempotentKeysRepository,
        ApiHeadersConfig $apiHeadersConfig,
        Emitter $hermesEmitter,
        HttpRequest $request,
        HttpResponse $response
    ) {
        $this->apiRoutersContainer = $apiRoutesContainer;
        $this->apiTokenStatsRepository = $apiTokenStatsRepository;
        $this->idempotentKeysRepository = $idempotentKeysRepository;
        $this->apiHeadersConfig = $apiHeadersConfig;
        $this->hermesEmitter = $hermesEmitter;
        $this->applicationConfig = $applicationConfig;
        $this->httpRequest = $request;
        $this->httpResponse = $response;
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

        // handle preflight request
        if ($this->httpRequest->isMethod('OPTIONS')) {
            // set allowed headers
            $this->httpResponse->addHeader(
                'Access-Control-Allow-Headers',
                $this->apiHeadersConfig->getAllowedHeaders()
            );

            // set allowed methods
            $this->httpResponse->addHeader(
                'Access-Control-Allow-Methods',
                $this->apiHeadersConfig->getAllowedHttpMethods()
            );

            if ($this->apiHeadersConfig->hasAllowedCredentialsHeader()) {
                $this->httpResponse->addHeader(
                    'Access-Control-Allow-Credentials',
                    'true'
                );
            }

            return new JsonApiResponse(HttpResponse::S204_NO_CONTENT, ['options' => 'ok']);
        }

        $version = $request->getParameter('version');
        $category = $request->getParameter('package');
        $action = $request->getParameter('apiAction');

        if (!isset($version, $category, $action)) {
            $response = new JsonApiResponse(HttpResponse::S404_NOT_FOUND, [
                'error' => sprintf('Unknown api call: version [%s], category [%s], action [%s]', $version, $category, $action)
            ]);
            $this->httpResponse->setCode(HttpResponse::S404_NOT_FOUND);
            return $response;
        }

        $apiIdentifier = new ApiIdentifier($version, $category, $action);
        $handler = $this->apiRoutersContainer->getHandler($apiIdentifier);
        if (!$handler) {
            $response = new JsonApiResponse(HttpResponse::S404_NOT_FOUND, [
                'error' => sprintf('Unknown api call: version [%s], category [%s], action [%s]', $version, $category, $action)
            ]);
            $this->httpResponse->setCode(HttpResponse::S404_NOT_FOUND);
            return $response;
        }

        /** @var ApiAuthorizationInterface $authorization */
        $authorization = $this->apiRoutersContainer->getAuthorization($apiIdentifier);
        if (!$authorization->authorized($handler->resource())) {
            $response = new JsonApiResponse(HttpResponse::S403_FORBIDDEN, [
                'status' => 'error',
                'message' => sprintf('Not authorized: %s', $authorization->getErrorMessage()),
                'error' => 'no_authorization',
            ]);
            $this->httpResponse->setCode(HttpResponse::S403_FORBIDDEN);
            return $response;
        }

        $paramsProcessor = new ParamsProcessor($handler->params());
        $params = $paramsProcessor->getValues();

        if ($handler instanceof ApiParamsValidatorInterface) {
            $response = $handler->validateParams($params);
            if ($response) {
                return $response;
            }
        } else {
            if ($paramsProcessor->isError()) {
                $response = new JsonApiResponse(HttpResponse::S400_BAD_REQUEST, [
                    'status' => 'error',
                    'code' => 'invalid_input',
                    'errors' => $paramsProcessor->getErrors()
                ]);
                $this->httpResponse->setCode(HttpResponse::S400_BAD_REQUEST);
                return $response;
            }
        }

        $path = $this->httpRequest->getUrl()->path;
        $headerIdempotencyKey = $this->httpRequest->getHeader('Idempotency-Key');
        $idempotencyKey = false;
        if ($headerIdempotencyKey && !$request->isMethod('GET')) {
            $idempotencyKey = $this->idempotentKeysRepository->findKey($path, $headerIdempotencyKey);
        }
        if ($headerIdempotencyKey) {
            $handler->setIdempotentKey($headerIdempotencyKey);
        }

        $handler->setAuthorization($authorization);

        if ($idempotencyKey && $handler instanceof IdempotentHandlerInterface) {
            $response = $handler->idempotentHandle($params);
        } else {
            $response = $handler->handle($params);
            if ($headerIdempotencyKey && $response->getHttpCode() == HttpResponse::S200_OK && $handler instanceof IdempotentHandlerInterface) {
                $this->idempotentKeysRepository->add($path, $headerIdempotencyKey);
            }
        }

        $this->log($apiIdentifier, $authorization, $response);

        $this->httpResponse->setCode($response->getCode());
        return $response;
    }

    private function log($apiIdentifier, $authorization, $response)
    {
        $apiLogEnabled = $this->applicationConfig->get('enable_api_log');
        $apiStatsEnabled = $this->applicationConfig->get('enable_api_stats');

        $token = '';
        if ($authorization instanceof BearerTokenAuthorization) {
            $tokenParser = new TokenParser();
            $token = $tokenParser->getToken();
            if (!$apiLogEnabled && $apiStatsEnabled) {
                // If we don't log API calls, but API stats yes, update token stats directly. Otherwise we'll update it asynchronously.
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

        if (isset($_GET['password'])) {
            $_GET['password'] = '***';
        }
        if (isset($_POST['password'])) {
            $_POST['password'] = '***';
        }

        $input = ['POST' => $_POST, 'GET' => $_GET];
        $jsonInput = json_encode($input);

        $elapsed = Debugger::timer() * 1000;
        $path = $apiIdentifier->getApiPath();
        $responseCode = $response->getCode();
        $ipAddress = \Crm\ApplicationModule\Request::getIp();
        $userAgent = \Crm\ApplicationModule\Request::getUserAgent();

        $this->hermesEmitter->emit(new HermesMessage('api-log', [
            'token' => $token,
            'path' => $path,
            'jsonInput' => $jsonInput,
            'responseCode' => $responseCode,
            'elapsed' => $elapsed,
            'ipAddress' => $ipAddress,
            'userAgent' => $userAgent,
        ]), HermesMessage::PRIORITY_LOW);
    }
}
