<?php

namespace Crm\ApiModule\Router;

use Crm\ApiModule\Api\ApiHandlerInterface;
use Crm\ApiModule\Api\ApiRouteInterface;
use Crm\ApiModule\Api\ApiRoutersContainerInterface;
use Crm\ApiModule\Authorization\ApiAuthorizationInterface;
use Nette\DI\Container;

class ApiRoutesContainer implements ApiRoutersContainerInterface
{
    /** @var ApiRouteInterface[] */
    private $routers = [];

    /** @var Container  */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function attachRouter(ApiRouteInterface $router): void
    {
        $this->routers[$router->getApiIdentifier()->getApiPath()] = $router;

        $handler = $this->getHandler($router->getApiIdentifier());
        $authorization = $this->getAuthorization($router->getApiIdentifier());

        if ($handler === null || $authorization === null) {
            throw new \Exception('Incorrectly configured API endpoint: [' .
                $router->getApiIdentifier()->getApiPath() .
                ']. Missing handler or authorization.');
        }

        $handler->setAuthorization($authorization);
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
