<?php

namespace Crm\ApiModule\Api;

use Tomaj\NetteApi\EndpointInterface;
use Tomaj\NetteApi\RateLimit\NoRateLimit;
use Tomaj\NetteApi\RateLimit\RateLimitInterface;

class LazyApi
{
    public function __construct(
        private EndpointInterface $endpoint,
        private string $handlerClassName,
        private string $authorizationClassName,
        private ?RateLimitInterface $rateLimit = null
    ) {
        $this->rateLimit = $rateLimit ?: new NoRateLimit();
    }

    public function getEndpoint(): EndpointInterface
    {
        return $this->endpoint;
    }

    public function getHandlerClassName(): string
    {
        return $this->handlerClassName;
    }

    public function getAuthorizationClassName(): string
    {
        return $this->authorizationClassName;
    }

    public function getRateLimit(): RateLimitInterface
    {
        return $this->rateLimit;
    }
}
