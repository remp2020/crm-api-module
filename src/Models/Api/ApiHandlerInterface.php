<?php

namespace Crm\ApiModule\Api;

use Crm\ApiModule\Authorization\ApiAuthorizationInterface;
use Nette\Application\Response;

interface ApiHandlerInterface
{
    /** @return Response */
    public function handle(ApiAuthorizationInterface $authorization);

    /** @return \Crm\ApiModule\Params\ParamInterface[] */
    public function params();

    public function resource(): string;

    public function setIdempotentKey(string $idempotentKey): void;
}
