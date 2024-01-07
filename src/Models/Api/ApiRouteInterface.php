<?php

namespace Crm\ApiModule\Models\Api;

use Crm\ApiModule\Router\ApiIdentifier;

interface ApiRouteInterface
{
    public function getApiIdentifier(): ApiIdentifier;

    public function getHandlerClassName(): string;

    public function getAuthorizationClassName(): string;
}
