<?php

namespace Crm\ApiModule\Models\Api;

use Crm\ApiModule\Router\ApiIdentifier;

interface ApiRoutersContainerInterface
{
    public function attachRouter(ApiRouteInterface $router);

    public function getHandlers();

    public function getHandler(ApiIdentifier $identifier);

    public function getAuthorization(ApiIdentifier $identifier);
}
