<?php

namespace Crm\ApiModule\Router;

use Crm\ApplicationModule\Api\ApiRouteInterface;
use Crm\ApplicationModule\Api\ApiRoutersContainerInterface;
use Nette\DI\Container;

class ApiRoutesContainer implements ApiRoutersContainerInterface
{
    /** @var array(ApiRouteInterface) */
    private $routers = [];

    /** @var Container  */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function attachRouter(ApiRouteInterface $router)
    {
        $newRouters = [];
        foreach ($this->routers as $r) {
            if (!$router->getApiIdentifier()->equals($r->getApiIdentifier())) {
                $newRouters[] = $r;
            }
        }
        $newRouters[] = $router;
        $this->routers = $newRouters;
    }

    /**
     * @return array(\Crm\ApplicationModule\Api\ApiHandlerInterface)
     */
    public function getHandlers()
    {
        $instances = [];
        foreach ($this->routers as $router) {
            $instances[] = $this->container->getByType($router->getHandlerClassName());
        }
        return $instances;
    }

    /**
     * @param ApiIdentifier $identifier
     * @return \Crm\ApplicationModule\Api\ApiHandlerInterface
     */
    public function getHandler(ApiIdentifier $identifier)
    {
        $router = $this->getRouter($identifier);
        if (!$router) {
            return false;
        }
        return $this->container->getByType($router->getHandlerClassName());
    }

    /**
     * @param ApiIdentifier $identifier
     * @return \Crm\ApplicationModule\Api\ApiRouteInterface
     */
    public function getRouter(ApiIdentifier $identifier)
    {
        foreach ($this->routers as $router) {
            if ($identifier->equals($router->getApiIdentifier())) {
                return $router;
            }
        }
        return false;
    }

    /**
     * @return array(\Crm\ApplicationModule\Api\ApiRouteInterface)
     */
    public function getRouters()
    {
        return $this->routers;
    }

    /**
     * @param ApiIdentifier $identifier
     * @return \Crm\ApiModule\Authorization\ApiAuthorizationInterface
     */
    public function getAuthorization(ApiIdentifier $identifier)
    {
        $router = $this->getRouter($identifier);
        if (!$router) {
            return false;
        }
        return $this->container->getByType($router->getAuthorizationClassName());
    }
}
