<?php

namespace Crm\ApiModule\Api;

use Crm\ApiModule\Response\ApiResponseInterface;

interface IdempotentHandlerInterface
{
    public function idempotentHandle(array $params): ApiResponseInterface;
}
