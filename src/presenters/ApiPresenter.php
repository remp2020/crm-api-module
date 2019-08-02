<?php

namespace Crm\ApiModule\Presenters;

use Crm\ApiModule\Api\IdempotentHandlerInterface;
use Crm\ApiModule\Api\JsonResponse;
use Crm\ApiModule\Authorization\BearerTokenAuthorization;
use Crm\ApiModule\Authorization\TokenParser;
use Crm\ApiModule\Repository\ApiLogsRepository;
use Crm\ApiModule\Repository\ApiTokenStatsRepository;
use Crm\ApiModule\Repository\IdempotentKeysRepository;
use Crm\ApiModule\Router\ApiIdentifier;
use Crm\ApiModule\Router\ApiRoutesContainer;
use Crm\ApplicationModule\Hermes\HermesMessage;
use Crm\ApplicationModule\Presenters\BasePresenter;
use Crm\ApplicationModule\Request;
use Crm\UsersModule\Auth\UserTokenAuthorization;
use Nette\Http\Response;
use Tomaj\Hermes\Emitter;
use Tracy\Debugger;

class ApiPresenter extends BasePresenter
{
    private $apiRoutersContainer;

    private $apiLogsRepository;

    private $apiTokenStatsRepository;

    private $idempotentKeysRepository;

    private $hermesEmitter;

    private $response;

    public function __construct(
        ApiRoutesContainer $apiRoutesContainer,
        ApiLogsRepository $apiLogsRepository,
        ApiTokenStatsRepository $apiTokenStatsRepository,
        IdempotentKeysRepository $idempotentKeysRepository,
        Emitter $hermesEmitter,
        Response $response
    ) {
        parent::__construct();
        $this->apiRoutersContainer = $apiRoutesContainer;
        $this->apiLogsRepository = $apiLogsRepository;
        $this->apiTokenStatsRepository = $apiTokenStatsRepository;
        $this->idempotentKeysRepository = $idempotentKeysRepository;
        $this->hermesEmitter = $hermesEmitter;
        $this->response = $response;
    }

    public function renderApi()
    {
        Debugger::timer();

        $this->getHttpResponse()->addHeader('Access-Control-Allow-Origin', '*');

        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            $this->response->addHeader('Access-Control-Allow-Headers', 'Authorization');
            $this->response->addHeader('Access-Control-Allow-Headers', 'X-Requested-With');
            $this->response->addHeader('Access-Control-Allow-Headers', 'Content-Type');
            $this->sendResponse(new JsonResponse(['options' => 'ok']));
        }

        $version = $this->params['version'];
        $category = $this->params['category'];
        $action = $this->params['apiaction'];

        $apiIdentifer = new ApiIdentifier($version, $category, $action);

        $authorization = $this->apiRoutersContainer->getAuthorization($apiIdentifer);
        $handler = $this->apiRoutersContainer->getHandler($apiIdentifer);

        if (!$handler) {
            $this->response->setCode(Response::S501_NOT_IMPLEMENTED);
            $result = new JsonResponse(['error' => sprintf('Unknown api call: version [%s], category [%s], action [%s]', $version, $category, $action)]);
        } else {
            if (!$authorization->authorized($handler->resource())) {
                $result = new JsonResponse([
                    'status' => 'error',
                    'message' => sprintf('Not authorized: %s', $authorization->getErrorMessage()),
                    'error' => 'no_authorization',
                ]);
                $this->response->setCode(Response::S403_FORBIDDEN);
            } else {
                $path = $this->getHttpRequest()->getUrl()->path;
                $headerIdempotentKey = $this->getHttpRequest()->getHeader('Idempotency-Key');
                $idempotentKey = false;
                if ($headerIdempotentKey && !$this->request->isMethod('GET')) {
                    $idempotentKey = $this->idempotentKeysRepository->findKey($path, $headerIdempotentKey);
                }
                if ($headerIdempotentKey) {
                    $handler->setIdempotentKey($headerIdempotentKey);
                }
                if ($idempotentKey && $handler instanceof IdempotentHandlerInterface) {
                    $result = $handler->idempotentHandle($authorization);
                } else {
                    $result = $handler->handle($authorization);
                    if ($headerIdempotentKey && $result->getHttpCode() == Response::S200_OK && $handler instanceof IdempotentHandlerInterface) {
                        $this->idempotentKeysRepository->add($path, $headerIdempotentKey);
                    }
                }
                $this->response->setCode($result->getHttpCode());
            }
        }

        $token = '';
        if ($authorization instanceof BearerTokenAuthorization) {
            $tokenParser = new TokenParser();
            $token = $tokenParser->getToken();
            $this->apiTokenStatsRepository->updateStats($token);
        }
        // TODO: [users_module] try to refactor this so ApiModule doesn't have dependency on UsersModule
        if ($authorization instanceof UserTokenAuthorization) {
            $tokenParser = new TokenParser();
            $token = $tokenParser->getToken();
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
        $path = $apiIdentifer->getApiPath();
        $responseCode = $this->response->getCode();
        $ipAddress = Request::getIp();
        $userAgent = Request::getUserAgent();

        if ($this->applicationConfig->get('enable_api_log')) {
            $this->hermesEmitter->emit(new HermesMessage('api-log', [
                'token' => $token,
                'path' => $path,
                'jsonInput' => $jsonInput,
                'responseCode' => $responseCode,
                'elapsed' => $elapsed,
                'ipAddress' => $ipAddress,
                'userAgent' => $userAgent,
            ]));
        }

        $this->sendResponse($result);
    }
}
