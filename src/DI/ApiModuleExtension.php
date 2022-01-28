<?php

namespace Crm\ApiModule\DI;

use Kdyby\Translation\DI\ITranslationProvider;
use Nette\DI\CompilerExtension;

final class ApiModuleExtension extends CompilerExtension implements ITranslationProvider
{
    public function loadConfiguration()
    {
        // load services from config and register them to Nette\DI Container
        $this->compiler->loadDefinitionsFromConfig(
            $this->loadFromFile(__DIR__.'/../config/config.neon')['services']
        );
    }

    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();

        $builder->getDefinition($builder->getByType(\Nette\Application\IPresenterFactory::class))
            ->addSetup('setMapping', [['Api' => 'Crm\ApiModule\Presenters\*Presenter']]);
    }

    /**
     * Return array of directories, that contain resources for translator.
     * @return string[]
     */
    public function getTranslationResources()
    {
        return [__DIR__ . '/../lang/'];
    }
}
