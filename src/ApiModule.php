<?php

namespace Crm\ApiModule;

use Crm\ApiModule\Api\ApiRoutersContainerInterface;
use Crm\ApiModule\Repository\ApiLogsRepository;
use Crm\ApiModule\Router\ApiIdentifier;
use Crm\ApiModule\Router\ApiRoute;
use Crm\ApiModule\Seeders\ConfigsSeeder;
use Crm\ApplicationModule\CallbackManagerInterface;
use Crm\ApplicationModule\Commands\CommandsContainerInterface;
use Crm\ApplicationModule\CrmModule;
use Crm\ApplicationModule\Menu\MenuContainerInterface;
use Crm\ApplicationModule\Menu\MenuItem;
use Crm\ApplicationModule\SeederManager;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;
use Nette\DI\Container;
use Tomaj\Hermes\Dispatcher;

class ApiModule extends CrmModule
{
    public function registerAdminMenuItems(MenuContainerInterface $menuContainer)
    {
        $mainMenu = new MenuItem('', '#', 'fa fa-link', 800);

        $menuItem = new MenuItem($this->translator->translate('api.menu.api_tokens'), ':Api:ApiTokensAdmin:', 'fa fa-unlink', 200);
        $mainMenu->addChild($menuItem);

        $menuItem = new MenuItem($this->translator->translate('api.menu.api_calls'), ':Api:ApiCallsAdmin:', 'fa fa-terminal', 300);
        $mainMenu->addChild($menuItem);

        $menuItem = new MenuItem($this->translator->translate('api.menu.api_logs'), ':Api:ApiLogsAdmin:', 'fa fa-list', 400);
        $mainMenu->addChild($menuItem);

        $menuItem = new MenuItem($this->translator->translate('api.menu.api_access'), ':Api:ApiAccessAdmin:', 'fa fa-lock', 500);
        $mainMenu->addChild($menuItem);

        $menuContainer->attachMenuItem($mainMenu);
    }

    public function registerCleanupFunction(CallbackManagerInterface $cleanUpManager)
    {
        $cleanUpManager->add(ApiLogsRepository::class, function (Container $container) {
            $apiLogsRepository = $container->getByType(ApiLogsRepository::class);
            $apiLogsRepository->removeOldData();
        });
    }

    public function registerCommands(CommandsContainerInterface $commandsContainer)
    {
        $commandsContainer->registerCommand($this->getInstance(\Crm\ApiModule\Commands\GenerateAccessCommand::class));
    }

    public function registerApiCalls(ApiRoutersContainerInterface $apiRoutersContainer)
    {
        $apiRoutersContainer->attachRouter(
            new ApiRoute(new ApiIdentifier('1', 'token', 'check'), \Crm\ApiModule\Api\TokenCheckHandler::class, \Crm\ApiModule\Authorization\BearerTokenAuthorization::class)
        );
    }

    public function registerRoutes(RouteList $router)
    {
        $router[] = new Route('api/v<version>/<category>[/<apiaction=default>]', 'Api:Api:api');
    }

    public function registerSeeders(SeederManager $seederManager)
    {
        $seederManager->addSeeder($this->getInstance(ConfigsSeeder::class));
    }

    public function registerHermesHandlers(Dispatcher $dispatcher)
    {
        $dispatcher->registerHandler(
            'api-log',
            $this->getInstance(\Crm\ApiModule\Hermes\ApiLogHandler::class)
        );
    }
}
