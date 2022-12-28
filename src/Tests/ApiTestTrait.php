<?php

namespace Crm\ApiModule\Tests;

use Crm\ApiModule\Api\ApiHandlerInterface;
use Crm\ApiModule\Api\Runner;
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
}
