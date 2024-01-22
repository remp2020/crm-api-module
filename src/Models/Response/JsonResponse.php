<?php

namespace Crm\ApiModule\Models\Response;

use Crm\ApiModule\Response\ApiResponseInterface;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\SmartObject;
use Nette\Utils\Json;

/**
 * @deprecated use \Tomaj\NetteApi\Response\JsonApiResponse
 */
class JsonResponse implements ApiResponseInterface
{
    use SmartObject;

    /** @var mixed */
    private $payload;

    /** @var string */
    private $contentType;

    private int $code;

    public function __construct($payload, string $contentType = null)
    {
        $this->payload = $payload;
        $this->contentType = $contentType ?: 'application/json; charset=utf-8';
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Returns the MIME content type of a downloaded file.
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * Sends response to output.
     */
    public function send(IRequest $httpRequest, IResponse $httpResponse): void
    {
        $httpResponse->setContentType($this->contentType, 'utf-8');
        echo Json::encode($this->payload);
    }

    /**
     * @deprecated use getCode()
     */
    public function getHttpCode()
    {
        return $this->getCode();
    }

    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @deprecated use setCode()
     */
    public function setHttpCode($httpCode)
    {
        $this->setCode($httpCode);
    }

    public function setCode(int $code)
    {
        $this->code = $code;
    }
}
