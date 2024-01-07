<?php

namespace Crm\ApiModule\Hermes;

use Crm\ApiModule\Repositories\ApiLogsRepository;
use Crm\ApiModule\Repositories\ApiTokenStatsRepository;
use Crm\ApplicationModule\Config\ApplicationConfig;
use Nette\Utils\Json;
use Tomaj\Hermes\Handler\HandlerInterface;
use Tomaj\Hermes\Handler\RetryTrait;
use Tomaj\Hermes\MessageInterface;

class ApiLogHandler implements HandlerInterface
{
    use RetryTrait;

    public function __construct(
        protected ApiLogsRepository $apiLogsRepository,
        protected ApiTokenStatsRepository $apiTokenStatsRepository,
        protected ApplicationConfig $applicationConfig
    ) {
    }

    public function handle(MessageInterface $message): bool
    {
        $payload = $message->getPayload();
        $input = Json::decode(Json::encode($payload['jsonInput'])); // strip whitespaces

        $this->apiLogsRepository->add(
            $payload['token'],
            $payload['path'],
            $input,
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
