<?php

namespace Crm\ApiModule\Api;

use Nette\DI\Container;
use Nette\Http\Response;
use Tomaj\NetteApi\Api;
use Tomaj\NetteApi\ApiDecider;
use Tomaj\NetteApi\Authorization\NoAuthorization;
use Tomaj\NetteApi\EndpointIdentifier;
use Tomaj\NetteApi\EndpointInterface;
use Tomaj\NetteApi\Handlers\ApiHandlerInterface;
use Tomaj\NetteApi\Handlers\CorsPreflightHandler;
use Tomaj\NetteApi\Handlers\DefaultHandler;
use Tomaj\NetteApi\RateLimit\RateLimitInterface;

class LazyApiDecider extends ApiDecider
{
    /** @var LazyApi[] */
    private array $lazyApis = [];

    private ?ApiHandlerInterface $globalPreflightHandler = null;

    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getApi(string $method, int $version, string $package, ?string $apiAction = null): Api
    {
        $method = strtoupper($method);
        $apiAction = $apiAction === '' ? null : $apiAction;

        foreach ($this->lazyApis as $lazyApi) {
            $identifier = $lazyApi->getEndpoint();
            if ($method === $identifier->getMethod() && $identifier->getVersion() === $version && $identifier->getPackage() === $package && $identifier->getApiAction() === $apiAction) {
                $endpointIdentifier = new EndpointIdentifier($method, $version, $package, $apiAction);
                $api = $this->convertLazyApi($lazyApi);
                $api->getHandler()->setEndpointIdentifier($endpointIdentifier);
                return $api;
            }
            if ($method === 'OPTIONS' && $this->globalPreflightHandler && $identifier->getVersion() === $version && $identifier->getPackage() === $package && $identifier->getApiAction() === $apiAction) {
                return new Api(new EndpointIdentifier('OPTIONS', $version, $package, $apiAction), $this->globalPreflightHandler, new NoAuthorization());
            }
        }
        return new Api(new EndpointIdentifier($method, $version, $package, $apiAction), new DefaultHandler(), new NoAuthorization());
    }

    public function enableGlobalPreflight(ApiHandlerInterface $corsHandler = null)
    {
        if (!$corsHandler) {
            $corsHandler = new CorsPreflightHandler(new Response());
        }
        $this->globalPreflightHandler = $corsHandler;
    }

    public function addLazyApi(EndpointInterface $endpointIdentifier, string $handlerClassName, string $apiAuthorizationClassName, RateLimitInterface $rateLimit = null): self
    {
        $this->lazyApis[] = new LazyApi($endpointIdentifier, $handlerClassName, $apiAuthorizationClassName, $this->container, $rateLimit);
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
        $lazyApi->getHandler()->setAuthorization($lazyApi->getAuthorization());
        $api = new Api($lazyApi->getEndpoint(), $lazyApi->getHandler(), $lazyApi->getAuthorization(), $lazyApi->getRateLimit());
        return $api;
    }
}
