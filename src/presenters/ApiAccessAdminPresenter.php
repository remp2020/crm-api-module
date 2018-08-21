<?php

namespace Crm\ApiModule\Presenters;

use Crm\ApiModule\Forms\ApiAccessResourceFormFactory;
use Crm\ApiModule\Forms\ApiAccessTokenFormFactory;
use Crm\ApiModule\Repository\ApiAccessRepository;
use Crm\ApiModule\Repository\ApiTokensRepository;
use Crm\ApiModule\Router\ApiRoutesContainer;
use Crm\AdminModule\Presenters\AdminPresenter;

class ApiAccessAdminPresenter extends AdminPresenter
{
    /** @var ApiAccessRepository @inject */
    public $apiAccessRepository;

    /** @var ApiTokensRepository @inject */
    public $apiTokensRepository;

    /** @var ApiRoutesContainer @inject */
    public $apiRoutesContainer;

    /** @var ApiAccessResourceFormFactory @inject */
    public $apiAccessResourceFormFactory;

    /** @var ApiAccessTokenFormFactory @inject */
    public $apiAccessTokenFormFactory;

    public function renderDefault()
    {
        $this->template->accessCount = $this->apiAccessRepository->all()->count('*');
        $this->template->apiAccesses = $this->apiAccessRepository->all();
        $this->template->tokenCount = $this->apiTokensRepository->all()->count('*');
        $this->template->apiTokens = $this->apiTokensRepository->all();
    }

    public function renderEditAccess($id)
    {
        $this->template->apiAccess = $this->apiAccessRepository->find($id);
    }

    public function renderEditToken($id)
    {
        $this->template->apiToken = $this->apiTokensRepository->find($id);
    }

    public function createComponentApiAccessResourceForm()
    {
        $apiAccess = $this->apiAccessRepository->find($this->getParameter('id'));
        $this->apiAccessResourceFormFactory->onSubmit = function () {
            $this->flashMessage($this->translator->trans('api.admin.access.form.update_success'));
            $this->redirect('ApiAccessAdmin:default');
        };
        return $this->apiAccessResourceFormFactory->create($apiAccess);
    }

    public function createComponentApiAccessTokenForm()
    {
        $token = $this->apiTokensRepository->find($this->getParameter('id'));
        $this->apiAccessTokenFormFactory->onSubmit = function () {
            $this->flashMessage($this->translator->trans('api.admin.access.form.update_success'));
            $this->redirect('ApiAccessAdmin:default');
        };
        return $this->apiAccessTokenFormFactory->create($token);
    }
}
