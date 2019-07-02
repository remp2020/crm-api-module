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

        $name = InternalToken::CONFIG_NAME;
        $apiToken = $this->configsRepository->loadByName($name);
        if (!$apiToken) {
            $tokenValue = TokenGenerator::generate();
            $this->configBuilder->createNew()
                ->setName($name)
                ->setDisplayName('Internal API token')
                ->setDescription('Token used for internal API calls')
                ->setType(ApplicationConfig::TYPE_STRING)
                ->setAutoload(true)
                ->setConfigCategory($category)
                ->setSorting(500)
                ->setValue($tokenValue)
                ->save();
            $output->writeln("  <comment>* config item <info>$name</info> created</comment>");

            $this->apiTokensRepository->insert([
                'name' => 'Internal token',
                'active' => 0,
                'token' => $tokenValue,
                'ip_restrictions' => '*',
                'created_at' => new DateTime(),
                'updated_at' => new DateTime(),
            ]);
        }

        $this->internalToken->addAccessToAllApiResources();
    }
}
