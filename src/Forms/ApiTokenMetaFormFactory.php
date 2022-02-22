<?php

namespace Crm\ApiModule\Forms;

use Crm\ApiModule\Repository\ApiTokenMetaRepository;
use Crm\ApiModule\Repository\ApiTokensRepository;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Database\UniqueConstraintViolationException;
use Nette\Localization\Translator;
use Nette\Utils\DateTime;
use Tomaj\Form\Renderer\BootstrapRenderer;

class ApiTokenMetaFormFactory
{
    private $apiTokensRepository;

    private $apiTokenMetaRepository;

    private $translator;

    public $onSave;

    public $onError;

    public function __construct(
        ApiTokensRepository $apiTokensRepository,
        ApiTokenMetaRepository $apiTokenMetaRepository,
        Translator $translator
    ) {
        $this->apiTokensRepository = $apiTokensRepository;
        $this->apiTokenMetaRepository = $apiTokenMetaRepository;
        $this->translator = $translator;
    }

    public function create(ActiveRow $apiToken)
    {
        $form = new Form();
        $form->setTranslator($this->translator);
        $form->setRenderer(new BootstrapRenderer());
        $form->getElementPrototype()->addAttributes(['class' => 'ajax']);

        $form->addText('key', 'api.admin.api_token_meta.form.key.label')
            ->setRequired('api.admin.api_token_meta.form.key.required');
        $form->addText('value', 'api.admin.api_token_meta.form.value.label')
            ->setRequired('api.admin.api_token_meta.form.value.required');
        $form->addHidden('api_token_id', $apiToken->id)
            ->setHtmlId('api_token_id');
        $form->addHidden('api_token_meta_id')
            ->setHtmlId('api_token_meta_id');
        $form->addSubmit('submit', 'api.admin.api_token_meta.form.submit');

        $form->onSuccess[] = [$this, 'formSucceeded'];

        return $form;
    }

    public function formSucceeded($form, $values)
    {
        $apiToken = $this->apiTokensRepository->find($values['api_token_id']);
        if ($values['api_token_meta_id']) {
            $meta = $this->apiTokenMetaRepository->find($values['api_token_meta_id']);
            if (!$meta) {
                $form->addError('api.admin.api_token_meta.error.internal');
                $this->onError->__invoke();
                return;
            }
            try {
                $this->apiTokenMetaRepository->update($meta, [
                    'key' => $values['key'],
                    'value' => $values['value'],
                    'updated_at' => new DateTime()
                ]);
            } catch (UniqueConstraintViolationException $e) {
                $form->addError('api.admin.api_token_meta.error.duplicate');
                $this->onError->__invoke();
                return;
            }
        } else {
            if (!$apiToken) {
                $form->addError('api.admin.api_token_meta.error.internal');
                $this->onError->__invoke();
                return;
            }

            try {
                $meta = $this->apiTokenMetaRepository->insert([
                    'api_token_id' => $apiToken->id,
                    'key' => $values['key'],
                    'value' => $values['value'],
                    'created_at' => new DateTime(),
                    'updated_at' => new DateTime(),
                ]);
            } catch (UniqueConstraintViolationException $e) {
                $form->addError('api.admin.api_token_meta.error.duplicate');
                $this->onError->__invoke();
                return;
            }
        }
        $this->onSave->__invoke($meta);
    }
}
