<?php

namespace Crm\ApiModule\Api;

use \Nette\Application\Responses\JsonResponse as NetteJsonResponse;
use Crm\ApiModule\Response\ApiResponseInterface;

class JsonResponse extends NetteJsonResponse implements ApiResponseInterface
{
    private $httpCode;

    public function __construct($payload)
    {
        parent::__construct($payload, 'application/json; charset=utf-8');
    }

    public function getHttpCode()
    {
        return $this->httpCode;
    }

    public function setHttpCode($httpCode)
    {
        $this->httpCode = $httpCode;
    }
}
