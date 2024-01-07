<?php

namespace Crm\ApiModule\Presenters;

use Crm\AdminModule\Presenters\AdminPresenter;
use Crm\ApiModule\Forms\ApiAccessResourceFormFactory;
use Crm\ApiModule\Forms\ApiAccessTokenFormFactory;
use Crm\ApiModule\Models\Router\ApiRoutesContainer;
use Crm\ApiModule\Repositories\ApiAccessRepository;
use Crm\ApiModule\Repositories\ApiTokensRepository;
use Nette\DI\Attributes\Inject;

class ApiAccessAdminPresenter extends AdminPresenter
{
    #[Inject]
    public ApiAccessRepository $apiAccessRepository;

    #[Inject]
    public ApiTokensRepository $apiTokensRepository;

    #[Inject]
    public ApiRoutesContainer $apiRoutesContainer;

    #[Inject]
    public ApiAccessResourceFormFactory $apiAccessResourceFormFactory;

    #[Inject]
    public ApiAccessTokenFormFactory $apiAccessTokenFormFactory;

    /**
     * @admin-access-level read
     */
    public function renderDefault()
    {
        $this->template->accessCount = $this->apiAccessRepository->all()->count('*');
        $this->template->apiAccesses = $this->apiAccessRepository->all();
        $this->template->tokenCount = $this->apiTokensRepository->all()->count('*');
        $this->template->apiTokens = $this->apiTokensRepository->all();
    }

    /**
     * @admin-access-level write
     */
    public function renderEditAccess($id)
    {
        $this->template->apiAccess = $this->apiAccessRepository->find($id);
    }

    /**
     * @admin-access-level write
     */
    public function renderEditToken($id)
    {
        $this->template->apiToken = $this->apiTokensRepository->find($id);
    }

    public function createComponentApiAccessResourceForm()
    {
        $apiAccess = $this->apiAccessRepository->find($this->getParameter('id'));
        $this->apiAccessResourceFormFactory->onSubmit = function () {
            $this->flashMessage($this->translator->translate('api.admin.access.form.update_success'));
            $this->redirect('ApiAccessAdmin:default');
        };
        return $this->apiAccessResourceFormFactory->create($apiAccess);
    }

    public function createComponentApiAccessTokenForm()
    {
        $token = $this->apiTokensRepository->find($this->getParameter('id'));
        $this->apiAccessTokenFormFactory->onSubmit = function () {
            $this->flashMessage($this->translator->translate('api.admin.access.form.update_success'));
            $this->redirect('ApiAccessAdmin:default');
        };
        return $this->apiAccessTokenFormFactory->create($token);
    }
}
