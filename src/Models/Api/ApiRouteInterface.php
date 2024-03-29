<?php

namespace Crm\ApiModule\Models\Api;

use Crm\ApiModule\Models\Router\ApiIdentifier;

interface ApiRouteInterface
{
    public function getApiIdentifier(): ApiIdentifier;

    public function getHandlerClassName(): string;

    public function getAuthorizationClassName(): string;
}
