<?php

namespace Crm\ApiModule\Models\Router;

use Crm\ApiModule\Api\ApiRouteInterface;

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

    public function getHandlerClassName(): string
    {
        return $this->handlerClassName;
    }

    public function getApiIdentifier(): ApiIdentifier
    {
        return $this->apiIdentifier;
    }

    public function getAuthorizationClassName(): string
    {
        return $this->authorizationClassName;
    }
}
