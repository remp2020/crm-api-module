<?php

namespace Crm\ApiModule\Presenters;

use Crm\AdminModule\Presenters\AdminPresenter;
use Crm\ApiModule\Api\LazyApiDecider;
use Crm\ApiModule\Forms\ApiTestCallFormFactory;
use Crm\ApiModule\Router\ApiIdentifier;
use Crm\ApiModule\Router\ApiRoutesContainer;
use Tomaj\NetteApi\Component\ApiConsoleControl;
use Tomaj\NetteApi\Component\ApiListingControl;

class ApiCallsAdminPresenter extends AdminPresenter
{
    /** @var ApiRoutesContainer @inject */
    public $apiRoutesContainer;

    /** @var ApiTestCallFormFactory @inject */
    public $apiTestCallFormFactory;

    /** @var LazyApiDecider @inject */
    public $lazyApiDecider;

    /**
     * @admin-access-level read
     */
    public function renderDefault()
    {
        $routers = $this->apiRoutesContainer->getRouters();
        $this->template->routers = $routers;
    }

    /**
     * @admin-access-level read
     */
    public function renderDetail($method, $version, $package, $apiAction)
    {
        $identifier = new ApiIdentifier($version, $package, $apiAction);
        $router = $this->apiRoutesContainer->getRouter($identifier);
        $handler = $this->apiRoutesContainer->resolveRouterHandler($router);

        $this->template->handler = $handler;
        $this->template->router = $router;
        $this->template->apiIdentifier = $identifier;
    }

    public function createComponentApiListingControl()
    {
        $control = new ApiListingControl($this->lazyApiDecider);
        $control->onClick[] = function ($method, $version, $package, $apiAction) {
            $this->redirect('detail', $method, $version, $package, $apiAction);
        };
        return $control;
    }

    protected function createComponentApiConsole()
    {
        $api = $this->lazyApiDecider->getApi(
            $this->params['method'],
            $this->params['version'],
            $this->params['package'],
            $this->params['apiAction'] ?? null
        );
        return new ApiConsoleControl(
            $this->getHttpRequest(),
            $api->getEndpoint(),
            $api->getHandler(),
            $api->getAuthorization()
        );
    }
}
