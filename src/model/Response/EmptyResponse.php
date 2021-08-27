<?php

namespace Crm\ApiModule\Api;

use Crm\ApiModule\Response\ApiResponseInterface;
use Nette;
use Nette\Http\Response;

class EmptyResponse implements ApiResponseInterface
{
    private $httpCode = Response::S204_NO_CONTENT;

    public function setHttpCode($httpCode)
    {
        $this->httpCode = $httpCode;
    }

    public function getHttpCode()
    {
        return $this->httpCode;
    }

    public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse): void
    {
        // nothing, void, emptiness...
    }
}
