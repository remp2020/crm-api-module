<?php

namespace Crm\ApiModule\Presenters;

use Crm\AdminModule\Presenters\AdminPresenter;
use Crm\ApiModule\Components\ApiListingControl;
use Crm\ApiModule\Models\Api\Lazy\LazyApiDecider;
use Crm\ApiModule\Models\Router\ApiIdentifier;
use Crm\ApiModule\Models\Router\ApiRoutesContainer;
use Nette\DI\Attributes\Inject;
use Tomaj\NetteApi\Component\ApiConsoleControl;

class ApiCallsAdminPresenter extends AdminPresenter
{
    #[Inject]
    public ApiRoutesContainer $apiRoutesContainer;

    #[Inject]
    public LazyApiDecider $lazyApiDecider;

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
        $handler = $this->apiRoutesContainer->getHandler($identifier);

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
