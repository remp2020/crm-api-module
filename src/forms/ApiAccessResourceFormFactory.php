<?php

namespace Crm\ApiModule\Forms;

use Crm\ApiModule\Repository\ApiAccessTokensRepository;
use Crm\ApiModule\Repository\ApiTokensRepository;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\Localization\ITranslator;
use Tomaj\Form\Renderer\BootstrapRenderer;

class ApiAccessResourceFormFactory
{
    private $apiTokensRepository;

    private $apiAccessTokensRepository;

    private $translator;

    public $onSubmit;

    public function __construct(
        ApiTokensRepository $apiTokensRepository,
        ApiAccessTokensRepository $apiAccessTokensRepository,
        ITranslator $translator
    ) {
        $this->apiTokensRepository = $apiTokensRepository;
        $this->apiAccessTokensRepository = $apiAccessTokensRepository;
        $this->translator = $translator;
    }

    public function create(ActiveRow $apiAccess)
    {
        $form = new Form;

        $form->addCheckbox('checkAll', $this->translator->translate('api.admin.access.form.check_all'))
            ->setOmitted(true)
            ->getControlPrototype()->addClass('checkAll');

        $form->addCheckboxList(
            'token_ids',
            $this->translator->translate('api.admin.access.form.tokens'),
            $this->apiTokensRepository->getTable()->fetchPairs('id', 'name')
        );
        $defaults = [
            'api_access_id' => $apiAccess->id,
            'token_ids' => $apiAccess->related('api_access_tokens')->fetchPairs('api_token_id', 'api_token_id'),
        ];

        $form->addHidden('api_access_id');

        $form->setRenderer(new BootstrapRenderer());
        $form->addProtection();

        $form->addSubmit('send', $this->translator->translate('api.admin.access.form.save'))
            ->getControlPrototype()
            ->setName('button')
            ->setHtml('<i class="fa fa-cloud-upload"></i> ' . $this->translator->translate('api.admin.access.form.save'));

        $form->setDefaults($defaults);

        $form->onSuccess[] = [$this, 'formSucceeded'];
        return $form;
    }

    public function formSucceeded($form, $values)
    {
        $this->apiAccessTokensRepository->getTable()->where([
            'api_access_id' => $values['api_access_id']
        ])->delete();
        foreach ($values['token_ids'] as $tokenId) {
            $this->apiAccessTokensRepository->insert([
                'api_access_id' => $values['api_access_id'],
                'api_token_id' => $tokenId,
            ]);
        }
        $this->onSubmit->__invoke($form);
    }
}
