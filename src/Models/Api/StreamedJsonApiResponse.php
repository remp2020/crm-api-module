<?php

declare(strict_types=1);

namespace Crm\ApiModule\Models\Api;

use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Tomaj\NetteApi\Response\ResponseInterface;

class StreamedJsonApiResponse implements ResponseInterface
{
    public function __construct(private int $code, private $callback)
    {
    }

    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * {@inheritdoc}
     */
    public function send(IRequest $httpRequest, IResponse $httpResponse): void
    {
        $httpResponse->setContentType('application/json', 'utf-8');
        ($this->callback)($httpRequest, $httpResponse);
    }
}
