<?php

namespace Crm\ApiModule\Api;

use Crm\ApiModule\Authorization\ApiAuthorizationInterface;

interface IdempotentHandlerInterface
{
    public function idempotentHandle(ApiAuthorizationInterface $authorization);
}
