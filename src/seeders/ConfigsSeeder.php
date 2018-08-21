<?php

namespace Crm\ApiModule\Seeders;

use Crm\ApplicationModule\Builder\ConfigBuilder;
use Crm\ApplicationModule\Config\ApplicationConfig;
use Crm\ApplicationModule\Config\Repository\ConfigCategoriesRepository;
use Crm\ApplicationModule\Config\Repository\ConfigsRepository;
use Crm\ApplicationModule\Seeders\ISeeder;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigsSeeder implements ISeeder
{
    private $configCategoriesRepository;

    private $configsRepository;

    private $configBuilder;

    public function __construct(
        ConfigCategoriesRepository $configCategoriesRepository,
        ConfigsRepository $configsRepository,
        ConfigBuilder $configBuilder
    ) {
        $this->configCategoriesRepository = $configCategoriesRepository;
        $this->configsRepository = $configsRepository;
        $this->configBuilder = $configBuilder;
    }

    public function seed(OutputInterface $output)
    {
        $category = $this->configCategoriesRepository->loadByName('Other');
        if (!$category) {
            $category = $this->configCategoriesRepository->add('Other', 'fa fa-tag', 900);
            $output->writeln('  <comment>* config category <info>Other</info> created</comment>');
        } else {
            $output->writeln(' * config category <info>Other</info> exists');
        }

        $name = 'enable_api_log';
        $config = $this->configsRepository->loadByName($name);
        if (!$config) {
            $this->configBuilder->createNew()
                ->setName($name)
                ->setDisplayName('API logs')
                ->setDescription('Enable API logs in database')
                ->setType(ApplicationConfig::TYPE_BOOLEAN)
                ->setAutoload(true)
                ->setConfigCategory($category)
                ->setSorting(500)
                ->setValue(true)
                ->save();
            $output->writeln("  <comment>* config item <info>$name</info> created</comment>");
        } else {
            $output->writeln("  * config item <info>$name</info> exists");
        }
    }
}
