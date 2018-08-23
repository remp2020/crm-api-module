<?php

namespace Crm\ApiModule\Commands;

use Crm\ApiModule\Repository\ApiAccessRepository;
use Crm\ApiModule\Router\ApiRoute;
use Crm\ApiModule\Router\ApiRoutesContainer;
use Crm\ApiModule\Api\ApiHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateAccessCommand extends Command
{
    private $apiRoutesContainer;

    private $apiAccessRepository;

    public function __construct(ApiRoutesContainer $apiRoutesContainer, ApiAccessRepository $apiAccessRepository)
    {
        parent::__construct();
        $this->apiRoutesContainer = $apiRoutesContainer;
        $this->apiAccessRepository = $apiAccessRepository;
    }

    protected function configure()
    {
        $this->setName('api:generate_access')
            ->setDescription('Generate all access data');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var ApiRoute[] $routers */
        $routers = $this->apiRoutesContainer->getRouters();

        foreach ($routers as $router) {
            $className = $router->getHandlerClassName();
            $output->write("Processing <info>{$className}</info>: ");
            $created = $this->processPresenterClass($className);
            $output->writeln($created ? 'Created' : 'OK');
        }
    }

    private function processPresenterClass($presenterClass)
    {
        $resource = ApiHandler::resourceFromClass($presenterClass);
        if ($this->apiAccessRepository->exists($resource)) {
            return false;
        }
        $result = $this->apiAccessRepository->add($resource);
        if (!$result) {
            throw new \Exception('unable to add new ApiAccess resource');
        }
        return true;
    }
}
