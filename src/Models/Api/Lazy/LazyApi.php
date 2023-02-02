<?php

namespace Crm\ApiModule\Api;

use Nette\DI\Container;
use Tomaj\NetteApi\Authorization\ApiAuthorizationInterface;
use Tomaj\NetteApi\EndpointInterface;
use Tomaj\NetteApi\Handlers\ApiHandlerInterface;
use Tomaj\NetteApi\RateLimit\NoRateLimit;
use Tomaj\NetteApi\RateLimit\RateLimitInterface;

class LazyApi
{
    private EndpointInterface $endpoint;

    private string $handlerClassName;

    private string $authorizationClassName;

    private RateLimitInterface $rateLimit;

    private Container $container;

    public function __construct(
        EndpointInterface $endpoint,
        string $handlerClassName,
        string $authorizationClassName,
        Container $container,
        ?RateLimitInterface $rateLimit = null
    ) {
        $this->endpoint = $endpoint;
        $this->handlerClassName = $handlerClassName;
        $this->authorizationClassName = $authorizationClassName;
        $this->container = $container;
        $this->rateLimit = $rateLimit ?: new NoRateLimit();
    }

    public function getEndpoint(): EndpointInterface
    {
        return $this->endpoint;
    }

    public function getHandler(): ApiHandler
    {
        return $this->container->getByType($this->handlerClassName);
    }

    public function getAuthorization(): ApiAuthorizationInterface
    {
        return $this->container->getByType($this->authorizationClassName);
    }

    public function getRateLimit(): RateLimitInterface
    {
        return $this->rateLimit;
    }
}
