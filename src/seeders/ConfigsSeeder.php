<?php

namespace Crm\ApiModule\Seeders;

use Crm\ApiModule\Repository\ApiTokensRepository;
use Crm\ApiModule\Token\InternalToken;
use Crm\ApplicationModule\Builder\ConfigBuilder;
use Crm\ApplicationModule\Config\ApplicationConfig;
use Crm\ApplicationModule\Config\Repository\ConfigCategoriesRepository;
use Crm\ApplicationModule\Config\Repository\ConfigsRepository;
use Crm\ApplicationModule\Seeders\ISeeder;
use Crm\UsersModule\Auth\Access\TokenGenerator;
use Nette\Utils\DateTime;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigsSeeder implements ISeeder
{
    private $configCategoriesRepository;

    private $configsRepository;

    private $configBuilder;

    private $apiTokensRepository;

    private $internalToken;

    public function __construct(
        ConfigCategoriesRepository $configCategoriesRepository,
        ConfigsRepository $configsRepository,
        ApiTokensRepository $apiTokensRepository,
        ConfigBuilder $configBuilder,
        InternalToken $internalToken
    ) {
        $this->configCategoriesRepository = $configCategoriesRepository;
        $this->configsRepository = $configsRepository;
        $this->configBuilder = $configBuilder;
        $this->apiTokensRepository = $apiTokensRepository;
        $this->internalToken = $internalToken;
    }

    public function seed(OutputInterface $output)
    {
        $categoryName = 'api.config.category';
        $category = $this->configCategoriesRepository->loadByName($categoryName);
        if (!$category) {
            $category = $this->configCategoriesRepository->add($categoryName, 'fa fa-tag', 900);
            $output->writeln('  <comment>* config category <info>API</info> created</comment>');
        } else {
            $output->writeln(' * config category <info>API</info> exists');
        }

        $name = 'enable_api_log';
        $config = $this->configsRepository->loadByName($name);
        if (!$config) {
            $this->configBuilder->createNew()
                ->setName($name)
                ->setDisplayName('api.config.enable_api_log.name')
                ->setDescription('api.config.enable_api_log.description')
                ->setType(ApplicationConfig::TYPE_BOOLEAN)
                ->setAutoload(true)
                ->setConfigCategory($category)
                ->setSorting(500)
                ->setValue(true)
                ->save();
            $output->writeln("  <comment>* config item <info>$name</info> created</comment>");
        } else {
            $output->writeln("  * config item <info>$name</info> exists");

            if ($config->category->name != $categoryName) {
                $this->configsRepository->update($config, [
                    'config_category_id' => $category->id
                ]);
                $output->writeln("  <comment>* config item <info>$name</info> updated</comment>");
            }
        }

        $name = 'enable_api_stats';
        $config = $this->configsRepository->loadByName($name);
        if (!$config) {
            $this->configBuilder->createNew()
                ->setName($name)
                ->setDisplayName('api.config.enable_api_stats.name')
                ->setDescription('api.config.enable_api_stats.description')
                ->setType(ApplicationConfig::TYPE_BOOLEAN)
                ->setAutoload(true)
                ->setConfigCategory($category)
                ->setSorting(499)
                ->setValue(true)
                ->save();
            $output->writeln("  <comment>* config item <info>$name</info> created</comment>");
        } else {
            $output->writeln("  * config item <info>$name</info> exists");

            if ($config->category->name != $categoryName) {
                $this->configsRepository->update($config, [
                    'config_category_id' => $category->id
                ]);
                $output->writeln("  <comment>* config item <info>$name</info> updated</comment>");
            }
        }

        $name = InternalToken::CONFIG_NAME;
        $apiToken = $this->configsRepository->loadByName($name);
        if (!$apiToken) {
            $tokenValue = TokenGenerator::generate();
            $this->configBuilder->createNew()
                ->setName($name)
                ->setDisplayName('api.config.internal_api_token.name')
                ->setDescription('api.config.internal_api_token.description')
                ->setType(ApplicationConfig::TYPE_STRING)
                ->setAutoload(true)
                ->setConfigCategory($category)
                ->setSorting(500)
                ->setValue($tokenValue)
                ->save();
            $output->writeln("  <comment>* config item <info>$name</info> created</comment>");

            $this->apiTokensRepository->insert([
                'name' => 'Internal token',
                'active' => 1,
                'token' => $tokenValue,
                'ip_restrictions' => '*',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ]);
        } else {
            $output->writeln("  * config item <info>$name</info> exists");

            if ($apiToken->category->name != $categoryName) {
                $this->configsRepository->update($apiToken, [
                    'config_category_id' => $category->id
                ]);
                $output->writeln("  <comment>* config item <info>$name</info> updated</comment>");
            }
        }

        $this->internalToken->addAccessToAllApiResources();
    }
}
