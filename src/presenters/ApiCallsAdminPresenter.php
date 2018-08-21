<?php

namespace Crm\ApiModule\Presenters;

use Crm\ApiModule\Forms\ApiTestCallFormFactory;
use Crm\ApiModule\Router\ApiIdentifier;
use Crm\ApiModule\Router\ApiRoutesContainer;
use Crm\AdminModule\Presenters\AdminPresenter;

class ApiCallsAdminPresenter extends AdminPresenter
{
    /** @var ApiRoutesContainer @inject */
    public $apiRoutesContainer;

    /** @var ApiTestCallFormFactory @inject */
    public $apiTestCallFormFactory;

    public function renderDefault()
    {
        $routers = $this->apiRoutesContainer->getRouters();
        $this->template->routers = $routers;
    }

    public function renderDetail($version, $category, $apiAction)
    {
        $identifier = new ApiIdentifier($version, $category, $apiAction);
        $router = $this->apiRoutesContainer->getRouter($identifier);
        $handler = $this->apiRoutesContainer->getHandler($identifier);

        $this->template->handler = $handler;
        $this->template->router = $router;
        $this->template->apiIdentifier = $identifier;
    }

    protected function createComponentApiTestCallForm()
    {
        $identifier = new ApiIdentifier($this->params['version'], $this->params['category'], $this->params['apiAction']);
        $form = $this->apiTestCallFormFactory->create($identifier);
        $this->apiTestCallFormFactory->onSubmit = function ($form, $result) {
            $this->template->callResult = $result;
        };
        return $form;
    }
}
