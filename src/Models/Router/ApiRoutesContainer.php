<?php

namespace Crm\ApiModule\Models\Router;

use Crm\ApiModule\Models\Api\ApiConfigurationException;
use Crm\ApiModule\Models\Api\ApiHandlerInterface;
use Crm\ApiModule\Models\Api\ApiRouteInterface;
use Crm\ApiModule\Models\Api\ApiRoutersContainerInterface;
use Crm\ApiModule\Models\Api\Lazy\LazyApiDecider;
use Crm\ApiModule\Models\Authorization\ApiAuthorizationInterface;
use Nette\DI\Container;

class ApiRoutesContainer implements ApiRoutersContainerInterface
{
    /** @var ApiRouteInterface[] */
    private array $routers = [];

    public function __construct(
        private Container $container,
        private LazyApiDecider $apiDecider
    ) {
    }

    public function attachRouter(ApiRouteInterface $router): void
    {
        $apiIdentifier = $router->getApiIdentifier();
        $this->routers[$apiIdentifier->getUrl()] = $router;

        $this->apiDecider->addApi(
            $apiIdentifier,
            $router->getHandlerClassName(),
            $router->getAuthorizationClassName(),
        );
    }

    /**
     * @return ApiHandlerInterface[]
     */
    public function getHandlers()
    {
        $handlers = [];
        foreach ($this->apiDecider->getApis() as $api) {
            /** @var ApiHandlerInterface $handler */
            $handler = $api->getHandler();
            $handlers[] = $handler;
        }
        return $handlers;
    }

    public function getHandler(ApiIdentifier $identifier): ?ApiHandlerInterface
    {
        $api = $this->apiDecider->getApi(
            $identifier->getMethod(),
            $identifier->getVersion(),
            $identifier->getPackage(),
            $identifier->getApiAction()
        );

        $handler = $api->getHandler();
        if (!($handler instanceof ApiHandlerInterface)) {
            throw new ApiConfigurationException(sprintf(
                "Invalid handler registered for API '%s %s'",
                $identifier->getMethod(),
                $identifier->getUrl(),
            ));
        }
        if (!($handler->getAuthorization() instanceof ApiAuthorizationInterface)) {
            throw new ApiConfigurationException(sprintf(
                "Invalid handler authorization registered for API '%s %s'",
                $identifier->getMethod(),
                $identifier->getUrl(),
            ));
        }

        return $handler;
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
        return $this->getHandler($identifier)->getAuthorization();
    }
}
