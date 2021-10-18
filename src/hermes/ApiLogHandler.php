<?php

namespace Crm\ApiModule\Hermes;

use Crm\ApiModule\Repository\ApiLogsRepository;
use Crm\ApiModule\Repository\ApiTokenStatsRepository;
use Tomaj\Hermes\Handler\HandlerInterface;
use Tomaj\Hermes\Handler\RetryTrait;
use Tomaj\Hermes\MessageInterface;

class ApiLogHandler implements HandlerInterface
{
    use RetryTrait;

    private $apiLogsRepository;

    private $apiTokenStatsRepository;

    public function __construct(ApiLogsRepository $apiLogsRepository, ApiTokenStatsRepository $apiTokenStatsRepository)
    {
        $this->apiLogsRepository = $apiLogsRepository;
        $this->apiTokenStatsRepository = $apiTokenStatsRepository;
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

        $this->apiTokenStatsRepository->updateStats($payload['token']);

        return true;
    }
}
