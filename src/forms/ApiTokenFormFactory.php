<?php

namespace Crm\ApiModule\Forms;

use Crm\ApiModule\Repository\ApiTokensRepository;
use Nette\Application\UI\Form;
use Nette\Localization\ITranslator;
use Tomaj\Form\Renderer\BootstrapRenderer;

class ApiTokenFormFactory
{
    protected $apiTokensRepository;

    private $translator;

    public $onSave;

    public $onUpdate;

    public function __construct(
        ApiTokensRepository $apiTokensRepository,
        ITranslator $translator
    ) {
        $this->apiTokensRepository = $apiTokensRepository;
        $this->translator = $translator;
    }

    /**
     * @param int $apiTokenId
     * @return Form
     */
    public function create($apiTokenId)
    {
        $defaults = [];
        if (isset($apiTokenId)) {
            $apiToken = $this->apiTokensRepository->find($apiTokenId);
            $defaults = $apiToken->toArray();
        }

        $form = new Form;

        $form->setRenderer(new BootstrapRenderer());
        $form->addProtection();

        $form->addText('name', $this->translator->translate('api.admin.api_tokens.fields.name.title'))
            ->setRequired($this->translator->translate('api.admin.api_tokens.fields.name.required'))
            ->setAttribute('placeholder', $this->translator->translate('api.admin.api_tokens.fields.name.placeholder'))
            ->setOption('description', $this->translator->translate('api.admin.api_tokens.fields.name.description'));

        $form->addTextArea('ip_restrictions', $this->translator->translate('api.admin.api_tokens.fields.ip_restrictions.title'))
            ->setAttribute('placeholder', $this->translator->translate('api.admin.api_tokens.fields.ip_restrictions.placeholder'))
            ->setOption('description', $this->translator->translate('api.admin.api_tokens.fields.ip_restrictions.description'));

        $form->addCheckbox('active', $this->translator->translate('api.admin.api_tokens.fields.active.title'));

        $form->addSubmit('send', $this->translator->translate('api.admin.api_tokens.form.submit'))
            ->getControlPrototype()
            ->setName('button')
            ->setHtml('<i class="fa fa-save"></i> ' . $this->translator->translate('api.admin.api_tokens.form.submit'));

        if ($apiTokenId) {
            $form->addHidden('api_token_id', $apiTokenId);
        }

        $form->setDefaults($defaults);

        $form->onSuccess[] = [$this, 'formSucceeded'];
        return $form;
    }

    public function formSucceeded($form, $values)
    {
        if (isset($values['api_token_id'])) {
            $id = $values['api_token_id'];
            unset($values['api_token_id']);
            $apiToken = $this->apiTokensRepository->find($id);
            $this->apiTokensRepository->update($apiToken, $values);
            $this->onUpdate->__invoke($form, $apiToken);
        } else {
            $apiToken = $this->apiTokensRepository->generate($values['name'], $values['ip_restrictions'], $values['active']);
            $this->onSave->__invoke($form, $apiToken);
        }
    }
}
