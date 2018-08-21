<?php

namespace Crm\ApiModule\Forms;

use Crm\ApiModule\Repository\ApiTokensRepository;
use Nette\Application\UI\Form;
use Tomaj\Form\Renderer\BootstrapRenderer;

class ApiTokenFormFactory
{
    /** @var ApiTokensRepository */
    protected $apiTokensRepository;

    public $onSave;

    public $onUpdate;

    public function __construct(ApiTokensRepository $apiTokensRepository)
    {
        $this->apiTokensRepository = $apiTokensRepository;
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

        $form->addText('name', 'Názov*:')
            ->setRequired('Názov musí byť vyplnený')
            ->setAttribute('placeholder', 'napište názov')
            ->setOption('description', 'nemá vplyv na funkčnosť, vhodné pre ľahšiu orientáciu');

        $form->addTextArea('ip_restrictions', 'IP reštrikcie:')
            ->setAttribute('placeholder', 'napríklad 132.42.88.33')
            ->setOption('description', 'napíšte zoznam ip oddelených čiarkov alebo použite znak *');

        $form->addCheckbox('active', 'Aktivovaný');

        $form->addSubmit('send', 'Ulož')
            ->getControlPrototype()
            ->setName('button')
            ->setHtml('<i class="fa fa-save"></i> Ulož');

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
