<?php

namespace Crm\ApiModule\Tests;

use Crm\ApiModule\Api\ApiHandlerInterface;
use Crm\ApiModule\Api\Runner;
use Tomaj\NetteApi\Response\JsonApiResponse;
use Tomaj\NetteApi\Response\ResponseInterface;

trait ApiTestTrait
{
    protected Runner $apiRunner;

    public function runApi(ApiHandlerInterface $apiHandler): ResponseInterface
    {
        if (!isset($this->apiRunner)) {
            $this->apiRunner = $this->inject(Runner::class);
        }
        return $this->apiRunner->run($apiHandler);
    }

    public function runJsonApi(ApiHandlerInterface $apiHandler): JsonApiResponse
    {
        $response = $this->runApi($apiHandler);
        $this->assertInstanceOf(JsonApiResponse::class, $response);
        return $response;
    }
}
