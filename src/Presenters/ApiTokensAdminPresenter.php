<?php

namespace Crm\ApiModule\Presenters;

use Crm\AdminModule\Presenters\AdminPresenter;
use Crm\ApiModule\Forms\ApiTokenFormFactory;
use Crm\ApiModule\Forms\ApiTokenMetaFormFactory;
use Crm\ApiModule\Models\Exception\CantDeleteActiveTokenException;
use Crm\ApiModule\Repositories\ApiTokenMetaRepository;
use Crm\ApiModule\Repositories\ApiTokensRepository;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\DI\Attributes\Inject;

class ApiTokensAdminPresenter extends AdminPresenter
{
    #[Inject]
    public ApiTokensRepository $apiTokensRepository;

    #[Inject]
    public ApiTokenFormFactory $apiTokenFormfactory;

    #[Inject]
    public ApiTokenMetaRepository $apiTokenMetaRepository;

    #[Inject]
    public ApiTokenMetaFormFactory $apiTokenMetaFormFactory;

    private $apiToken;

    /**
     * @admin-access-level read
     */
    public function renderDefault()
    {
        $this->template->apiTokens = $this->apiTokensRepository->all();
    }

    /**
     * @admin-access-level write
     */
    public function renderNew()
    {
    }

    /**
     * @admin-access-level write
     */
    public function renderEdit($id)
    {
        $apiToken = $this->apiTokensRepository->find($id);
        if (!$apiToken) {
            throw new BadRequestException();
        }
        $this->template->apiToken = $apiToken;
    }

    /**
     * @admin-access-level write
     */
    public function renderDelete($id)
    {
        $apiToken = $this->apiTokensRepository->find($id);

        try {
            $this->apiTokensRepository->delete($apiToken);
            $this->flashMessage($this->translator->translate('api.admin.api_tokens.message.removed'));
        } catch (CantDeleteActiveTokenException $e) {
            $this->flashMessage($this->translator->translate('api.admin.api_tokens.message.cant_delete_active_token'), 'error');
        }

        $this->redirect('default');
    }

    /**
     * @admin-access-level read
     */
    public function actionShow($id)
    {
        $this->apiToken = $this->apiTokensRepository->find($id);
        if (!$this->apiToken) {
            $this->flashMessage($this->translator->translate('api.admin.api_tokens.message.not_found'));
            $this->redirect('default');
        }

        $this->template->apiToken = $this->apiToken;
        $this->template->meta = $this->apiToken->related('api_token_meta');
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

    protected function createComponentApiTokenMetaForm(): Form
    {
        $form = $this->apiTokenMetaFormFactory->create($this->apiToken);

        $this->apiTokenMetaFormFactory->onSave = function ($meta) {
            $this->flashMessage($this->translator->translate('api.admin.api_token_meta.value_added'));
            if ($this->isAjax()) {
                $this->redrawControl('apiTokenMetaSnippet');
            } else {
                $this->redirect('show', $meta['api_token_id']);
            }
        };
        $this->apiTokenMetaFormFactory->onError = function () {
            if ($this->isAjax()) {
                $this->redrawControl('metaFormSnippet');
            }
        };

        return $form;
    }

    /**
     * @admin-access-level write
     */
    public function handleRemoveApiTokenMeta($metaId)
    {
        $meta = $this->apiTokenMetaRepository->find($metaId);
        $this->apiTokenMetaRepository->delete($meta);

        $this->flashMessage($this->translator->translate('api.admin.api_token_meta.value_removed'));
        if ($this->isAjax()) {
            $this->redrawControl('apiTokenMetaSnippet');
        } else {
            $apiTokenId = $meta['api_token_id'];
            $this->redirect('show', $apiTokenId);
        }
    }
}
