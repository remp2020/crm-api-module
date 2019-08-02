<?php

namespace Crm\ApiModule\Hermes;

use Crm\ApiModule\Repository\ApiLogsRepository;
use Tomaj\Hermes\Handler\HandlerInterface;
use Tomaj\Hermes\Handler\RetryTrait;
use Tomaj\Hermes\MessageInterface;

class ApiLogHandler implements HandlerInterface
{
    use RetryTrait;

    private $apiLogsRepository;

    public function __construct(ApiLogsRepository $apiLogsRepository)
    {
        $this->apiLogsRepository = $apiLogsRepository;
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

        return true;
    }
}
