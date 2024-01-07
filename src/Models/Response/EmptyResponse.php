<?php

namespace Crm\ApiModule\Models\Response;

use Crm\ApiModule\Response\ApiResponseInterface;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Http\Response;

class EmptyResponse implements ApiResponseInterface
{
    private int $code = Response::S204_NO_CONTENT;

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

    public function send(IRequest $httpRequest, IResponse $httpResponse): void
    {
        // nothing, void, emptiness...
    }
}
