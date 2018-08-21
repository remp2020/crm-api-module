<?php

namespace Crm\ApiModule\Router;

use Crm\ApplicationModule\Api\ApiRouteInterface;

class ApiRoute implements ApiRouteInterface
{
    /** @var ApiIdentifier  */
    private $apiIdentifier;

    private $handlerClassName;

    private $authorizationClassName;

    public function __construct(ApiIdentifier $apiIdentifier, $handlerClassName, $authorizationClassName)
    {
        $this->apiIdentifier = $apiIdentifier;
        $this->handlerClassName = $handlerClassName;
        $this->authorizationClassName = $authorizationClassName;
    }

    public function getHandlerClassName()
    {
        return $this->handlerClassName;
    }

    public function getApiIdentifier()
    {
        return $this->apiIdentifier;
    }

    public function getAuthorizationClassName()
    {
        return $this->authorizationClassName;
    }
}
