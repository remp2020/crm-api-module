<?php

namespace Crm\ApiModule\Router;

use Crm\ApiModule\Api\ApiHandlerInterface;
use Crm\ApiModule\Api\ApiRouteInterface;
use Crm\ApiModule\Api\ApiRoutersContainerInterface;
use Crm\ApiModule\Authorization\ApiAuthorizationInterface;
use Nette\DI\Container;
use Tomaj\NetteApi\ApiDecider;

class ApiRoutesContainer implements ApiRoutersContainerInterface
{
    /** @var ApiRouteInterface[] */
    private array $routers = [];

    public function __construct(
        private Container $container,
        private ApiDecider $apiDecider
    ) {
    }

    public function attachRouter(ApiRouteInterface $router): void
    {
        // TODO: remove attaching to routers
        $apiIdentifier = $router->getApiIdentifier();
        $this->routers[$apiIdentifier->getUrl()] = $router;

        // hacking around issue with handlers not knowing which authorization is used in tomaj/nette-api
        $handler = $this->resolveRouterHandler($router);
        $authorization = $this->resolveRouterAuthorization($router);
        if ($handler === null || $authorization === null) {
            throw new \Exception('Incorrectly configured API endpoint: [' .
                $router->getApiIdentifier()->getUrl() .
                ']. Missing handler or authorization.');
        }

        $handler->setAuthorization($authorization);
        $this->apiDecider->addApi(
            $apiIdentifier,
            $handler,
            $authorization
        );
    }

    /**
     * @return ApiHandlerInterface[]
     */
    public function getHandlers()
    {
        $instances = [];
        foreach ($this->routers as $router) {
            $instances[] = $this->container->getByType($router->getHandlerClassName());
        }
        return $instances;
    }

    public function getHandler(ApiIdentifier $identifier): ?ApiHandlerInterface
    {
        $router = $this->getRouter($identifier);
        if (!$router) {
            return null;
        }
        return $this->container->getByType($router->getHandlerClassName());
    }

    public function getRouter(ApiIdentifier $identifier): ?ApiRouteInterface
    {
        foreach ($this->routers as $router) {
            if ($identifier->equals($router->getApiIdentifier())) {
                return $router;
            }
        }
        return null;
    }

    public function resolveRouterHandler(ApiRouteInterface $router): ?ApiHandlerInterface
    {
        return $this->container->getByType($router->getHandlerClassName());
    }

    public function resolveRouterAuthorization(ApiRouteInterface $router): ?ApiAuthorizationInterface
    {
        return $this->container->getByType($router->getAuthorizationClassName());
    }

    /**
     * @return ApiRouteInterface[]
     */
    public function getRouters(): array
    {
        return array_values($this->routers);
    }

    public function getAuthorization(ApiIdentifier $identifier): ?ApiAuthorizationInterface
    {
        $router = $this->getRouter($identifier);
        if (!$router) {
            return null;
        }
        return $this->container->getByType($router->getAuthorizationClassName());
    }
}
