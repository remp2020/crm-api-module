<?php

namespace Crm\ApiModule\Api;

interface ApiRouteInterface
{
    public function getApiIdentifier();

    public function getHandlerClassName();

    public function getAuthorizationClassName();
}
