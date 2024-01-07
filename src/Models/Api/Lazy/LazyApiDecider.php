<?php

namespace Crm\ApiModule\Models\Api\Lazy;

use Crm\ApiModule\Models\Router\ApiIdentifier;
use Nette\DI\Container;
use Tomaj\NetteApi\Api;
use Tomaj\NetteApi\ApiDecider;
use Tomaj\NetteApi\EndpointInterface;
use Tomaj\NetteApi\RateLimit\RateLimitInterface;

/**
 * LazyApiDecider is a lazy implementation of Tomaj\NetteApi\ApiDecider. This might be merged in the future, but until
 * then we need to prevent instantiation of all API handlers before they're actually needed.
 */
class LazyApiDecider
{
    /** @var LazyApi[] */
    private array $lazyApis = [];

    public function __construct(
        private Container $container,
        private ApiDecider $apiDecider,
    ) {
    }

    public function getApi(string $method, int $version, string $package, ?string $apiAction = null): Api
    {
        $method = strtoupper($method);
        $apiAction = $apiAction === '' ? null : $apiAction;

        $identifierKey = $this->getEndpointIdentifierString(
            new ApiIdentifier($version, $package, $apiAction, $method)
        );
        $lazyApi = $this->lazyApis[$identifierKey] ?? null;

        if ($lazyApi === null) {
            // Previously our APIs didn't register the method and used GET method as a default. Let's temporarily try to also
            // find the GET-based handler, even if app is looking for POST request.
            $identifierKey = $this->getEndpointIdentifierString(
                new ApiIdentifier($version, $package, $apiAction, 'GET')
            );
            $lazyApi = $this->lazyApis[$identifierKey] ?? null;
        }

        if ($lazyApi !== null) {
            return $this->convertLazyApi($lazyApi);
        }

        // If the API handler wasn't registered via LazyApiDecider, use the original one to maintain backwards compatibility.
        return $this->apiDecider->getApi($method, $version, $package, $apiAction);
    }

    public function addApi(EndpointInterface $endpointIdentifier, string $handlerClassName, string $apiAuthorizationClassName, RateLimitInterface $rateLimit = null): self
    {
        $identifierKey = $this->getEndpointIdentifierString($endpointIdentifier);
        $this->lazyApis[$identifierKey] = new LazyApi($endpointIdentifier, $handlerClassName, $apiAuthorizationClassName, $rateLimit);
        return $this;
    }

    /**
     * @return Api[]
     */
    public function getApis(): array
    {
        return array_map(function (LazyApi $lazyApi) {
            return $this->convertLazyApi($lazyApi);
        }, $this->lazyApis);
    }

    private function convertLazyApi(LazyApi $lazyApi): Api
    {
        $authorization = $this->container->getByType($lazyApi->getAuthorizationClassName());

        $handler = $this->container->getByType($lazyApi->getHandlerClassName());
        $handler->setAuthorization($authorization);
        $handler->setEndpointIdentifier($lazyApi->getEndpoint());

        return new Api(
            endpoint: $lazyApi->getEndpoint(),
            handler: $handler,
            authorization: $authorization,
            rateLimit: $lazyApi->getRateLimit(),
        );
    }

    private function getEndpointIdentifierString(EndpointInterface $endpointIdentifier): string
    {
        return sprintf("%s_%s", $endpointIdentifier->getMethod(), $endpointIdentifier->getUrl());
    }
}
