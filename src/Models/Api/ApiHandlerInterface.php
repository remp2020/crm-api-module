<?php

namespace Crm\ApiModule\Models\Api;

use Crm\ApiModule\Models\Authorization\ApiAuthorizationInterface;
use Tomaj\NetteApi\Handlers\ApiHandlerInterface as TomajApiHandlerInterface;

interface ApiHandlerInterface extends TomajApiHandlerInterface
{
    public function resource(): string;

    public function setIdempotentKey(string $idempotentKey): void;

    public function setAuthorization(ApiAuthorizationInterface $authorization): void;

    public function getAuthorization(): ApiAuthorizationInterface;
}
