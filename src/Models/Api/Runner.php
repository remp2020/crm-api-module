<?php

namespace Crm\ApiModule\Models\Api;

use Crm\ApiModule\Repositories\IdempotentKeysRepository;
use Nette\Http\Request;
use Nette\Http\Response;
use Tomaj\NetteApi\Params\ParamsProcessor;
use Tomaj\NetteApi\Response\JsonApiResponse;
use Tomaj\NetteApi\Response\ResponseInterface;
use Tracy\Debugger;

class Runner
{
    public function __construct(
        private Request $httpRequest,
        private IdempotentKeysRepository $idempotentKeysRepository,
    ) {
    }

    public function run(ApiHandlerInterface $handler): ResponseInterface
    {
        $paramsProcessor = new ParamsProcessor($handler->params());
        $params = $paramsProcessor->getValues();

        if ($handler instanceof ApiParamsValidatorInterface) {
            $response = $handler->validateParams($params);
            if ($response) {
                return $response;
            }
        } else {
            if ($paramsProcessor->isError()) {
                $response = new JsonApiResponse(Response::S400_BAD_REQUEST, [
                    'status' => 'error',
                    'code' => 'invalid_input',
                    'errors' => $paramsProcessor->getErrors()
                ]);
                return $response;
            }
        }

        $path = $this->httpRequest->getUrl()->path;
        $headerIdempotencyKey = $this->httpRequest->getHeader('Idempotency-Key');
        $idempotencyKey = false;
        if ($headerIdempotencyKey && !$this->httpRequest->isMethod('GET')) {
            $idempotencyKey = $this->idempotentKeysRepository->findKey($path, $headerIdempotencyKey);
        }
        if ($headerIdempotencyKey) {
            $handler->setIdempotentKey($headerIdempotencyKey);
        }

        try {
            if ($idempotencyKey && $handler instanceof IdempotentHandlerInterface) {
                $response = $handler->idempotentHandle($params);
            } else {
                $response = $handler->handle($params);
                if ($headerIdempotencyKey && $response->getCode() == Response::S200_OK && $handler instanceof IdempotentHandlerInterface) {
                    $this->idempotentKeysRepository->add($path, $headerIdempotencyKey);
                }
            }
        } catch (\Throwable $exception) {
            $response = new JsonApiResponse(Response::S500_INTERNAL_SERVER_ERROR, [
                'status' => 'error',
                'code' => 'internal_server_error',
            ]);

            Debugger::log($exception, Debugger::EXCEPTION);
            return $response;
        }

        return $response;
    }
}
