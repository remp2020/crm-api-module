<?php

namespace Crm\ApiModule\Forms;

use Crm\ApiModule\Repository\ApiAccessRepository;
use Crm\ApiModule\Repository\ApiAccessTokensRepository;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Localization\Translator;
use Tomaj\Form\Renderer\BootstrapRenderer;

class ApiAccessTokenFormFactory
{
    private $apiAccessRepository;

    private $apiAccessTokensRepository;

    private $translator;

    public $onSubmit;

    public function __construct(
        ApiAccessRepository $apiAccessRepository,
        ApiAccessTokensRepository $apiAccessTokensRepository,
        Translator $translator
    ) {
        $this->apiAccessRepository = $apiAccessRepository;
        $this->apiAccessTokensRepository = $apiAccessTokensRepository;
        $this->translator = $translator;
    }

    public function create(ActiveRow $apiToken)
    {
        $form = new Form;

        $form->addCheckbox('checkAll', $this->translator->translate('api.admin.access.form.check_all'))
            ->setOmitted(true)
            ->getControlPrototype()->addClass('checkAll');

        $form->addHidden('api_token_id', $apiToken->id);

        $form->addCheckboxList('api_access_ids', $this->translator->translate('api.admin.access.form.resources'), $this->apiAccessRepository->getTable()->fetchPairs('id', 'resource'));
        $defaults = [
            'api_token_id' => $apiToken->id,
            'api_access_ids' => $apiToken->related('api_access_tokens')->fetchPairs('api_access_id', 'api_access_id'),
        ];

        $form->setRenderer(new BootstrapRenderer());
        $form->addProtection();

        $form->addSubmit('send')
            ->getControlPrototype()
            ->setName('button')
            ->setHtml('<i class="fa fa-save"></i> ' . $this->translator->translate('api.admin.access.form.save'));

        $form->setDefaults($defaults);

        $form->onSuccess[] = [$this, 'formSucceeded'];
        return $form;
    }

    public function formSucceeded($form, $values)
    {
        $this->apiAccessTokensRepository->getTable()->where([
            'api_token_id' => $values['api_token_id']
        ])->delete();
        foreach ($values['api_access_ids'] as $apiAccessId) {
            $this->apiAccessTokensRepository->insert([
                'api_token_id' => $values['api_token_id'],
                'api_access_id' => $apiAccessId,
            ]);
        }
        $this->onSubmit->__invoke($form);
    }
}
