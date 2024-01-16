<?php

namespace Crm\ApiModule\DI;

use Contributte\Translation\DI\TranslationProviderInterface;
use Nette\Application\IPresenterFactory;
use Nette\DI\CompilerExtension;

final class ApiModuleExtension extends CompilerExtension implements TranslationProviderInterface
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

        $builder->getDefinition($builder->getByType(IPresenterFactory::class))
            ->addSetup('setMapping', [['Api' => 'Crm\ApiModule\Presenters\*Presenter']]);
    }

    public function getTranslationResources(): array
    {
        return [__DIR__ . '/../lang/'];
    }
}
