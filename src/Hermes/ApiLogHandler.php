<?php

namespace Crm\ApiModule\Hermes;

use Crm\ApiModule\Repository\ApiLogsRepository;
use Crm\ApiModule\Repository\ApiTokenStatsRepository;
use Crm\ApplicationModule\Config\ApplicationConfig;
use Tomaj\Hermes\Handler\HandlerInterface;
use Tomaj\Hermes\Handler\RetryTrait;
use Tomaj\Hermes\MessageInterface;

class ApiLogHandler implements HandlerInterface
{
    use RetryTrait;

    private $apiLogsRepository;

    private $apiTokenStatsRepository;

    private $applicationConfig;

    public function __construct(ApiLogsRepository $apiLogsRepository, ApiTokenStatsRepository $apiTokenStatsRepository, ApplicationConfig $applicationConfig)
    {
        $this->apiLogsRepository = $apiLogsRepository;
        $this->apiTokenStatsRepository = $apiTokenStatsRepository;
        $this->applicationConfig = $applicationConfig;
    }

    public function handle(MessageInterface $message): bool
    {
        $payload = $message->getPayload();

        $this->apiLogsRepository->add(
            $payload['token'],
            $payload['path'],
            $payload['jsonInput'],
            $payload['responseCode'],
            $payload['elapsed'],
            $payload['ipAddress'],
            $payload['userAgent']
        );

        $apiStatsEnabled = $this->applicationConfig->get('enable_api_stats');
        if ($apiStatsEnabled) {
            $this->apiTokenStatsRepository->updateStats($payload['token']);//povodna line
        }

        return true;
    }
}
