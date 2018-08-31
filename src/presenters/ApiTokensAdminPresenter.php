<?php

namespace Crm\ApiModule\Presenters;

use Crm\ApiModule\Forms\ApiTokenFormFactory;
use Crm\ApiModule\Repository\ApiTokensRepository;
use Crm\AdminModule\Presenters\AdminPresenter;
use Nette\Application\BadRequestException;

class ApiTokensAdminPresenter extends AdminPresenter
{
    /** @var ApiTokensRepository @inject */
    public $apiTokensRepository;

    /** @var  ApiTokenFormFactory @inject */
    public $apiTokenFormfactory;

    public function renderDefault()
    {
        $this->template->apiTokens = $this->apiTokensRepository->all();
    }

    public function renderNew()
    {
    }

    public function renderEdit($id)
    {
        $apiToken = $this->apiTokensRepository->find($id);
        if (!$apiToken) {
            throw new BadRequestException();
        }
        $this->template->apiToken = $apiToken;
    }

    public function renderDelete($id)
    {
        $apiToken = $this->apiTokensRepository->find($id);
        $this->apiTokensRepository->delete($apiToken);
        $this->flashMessage($this->translator->translate('api.admin.api_tokens.message.removed'));
        $this->redirect('default');
    }

    public function createComponentApiTokenForm()
    {
        $id = null;
        if (isset($this->params['id'])) {
            $id = $this->params['id'];
        }

        $form = $this->apiTokenFormfactory->create($id);
        $this->apiTokenFormfactory->onSave = function ($form, $apiKey) {
            $this->flashMessage($this->translator->translate('api.admin.api_tokens.message.saved'));
            $this->redirect('ApiTokensAdmin:default');
        };
        $this->apiTokenFormfactory->onUpdate = function ($form, $apiKey) {
            $this->flashMessage($this->translator->translate('api.admin.api_tokens.message.updated'));
            $this->redirect('ApiTokensAdmin:default');
        };
        return $form;
    }
}
