<?php

namespace Crm\ApiModule\Api;

use Crm\ApiModule\Authorization\ApiAuthorizationInterface;
use Crm\ApiModule\Response\ApiResponseInterface;
use Tomaj\NetteApi\Handlers\ApiHandlerInterface as TomajApiHandlerInterface;

interface ApiHandlerInterface extends TomajApiHandlerInterface
{
    public function handle(array $params): ApiResponseInterface;

    public function resource(): string;

    public function setIdempotentKey(string $idempotentKey): void;

    public function setAuthorization(ApiAuthorizationInterface $authorization): void;

    public function getAuthorization(): ApiAuthorizationInterface;
}
