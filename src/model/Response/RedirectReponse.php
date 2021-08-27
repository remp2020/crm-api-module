<?php

namespace Crm\ApiModule\Api;

use Crm\ApiModule\Response\ApiResponseInterface;
use Nette;
use Nette\Http\Response;

class RedirectResponse implements ApiResponseInterface
{
    private $httpCode = Response::S307_TEMPORARY_REDIRECT;

    private $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

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
        header("Location: {$this->url}");
    }
}
