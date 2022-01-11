<?php

namespace Crm\ApiModule\Api;

use Crm\ApiModule\Authorization\ApiAuthorizationInterface;
use Crm\ApiModule\Params\ParamInterface;
use Crm\ApiModule\Response\ApiResponseInterface;

interface ApiHandlerInterface
{
    public function handle(array $params): ApiResponseInterface;

    /** @return ParamInterface[] */
    public function params(): array;

    public function resource(): string;

    public function setIdempotentKey(string $idempotentKey): void;

    public function setAuthorization(ApiAuthorizationInterface $authorization): void;

    public function getAuthorization(): ApiAuthorizationInterface;
}
