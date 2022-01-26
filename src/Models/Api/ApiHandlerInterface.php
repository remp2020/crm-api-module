<?php

namespace Crm\ApiModule\Api;

use Crm\ApiModule\Authorization\ApiAuthorizationInterface;

interface ApiHandlerInterface
{
    /** @return \Nette\Application\IResponse */
    public function handle(ApiAuthorizationInterface $authorization);

    /** @return \Crm\ApiModule\Params\ParamInterface[] */
    public function params();

    public function resource(): string;

    public function setIdempotentKey(string $idempotentKey): void;
}
