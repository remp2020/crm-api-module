<?php

namespace Crm\ApiModule\Api;

use Crm\ApiModule\Response\ApiResponseInterface;
use Nette;
use Spatie\ArrayToXml\ArrayToXml;

class XmlResponse implements ApiResponseInterface
{
    protected $httpCode;

    protected $rootElement;

    protected $rootElementAttributes;

    protected $payload;

    public function __construct(array $payload, string $rootElement, array $rootElementAttributes = [])
    {
        $this->payload = $payload;
        $this->rootElement = $rootElement;
        $this->rootElementAttributes = $rootElementAttributes;
    }

    public function getHttpCode()
    {
        return $this->httpCode;
    }

    public function setHttpCode($httpCode)
    {
        $this->httpCode = $httpCode;
    }

    public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse)
    {
        $httpResponse->setContentType('application/xml', 'utf-8');
        echo ArrayToXml::convert($this->payload, [
            'rootElementName' => $this->rootElement,
            '_attributes' => $this->rootElementAttributes,
        ], true, 'UTF-8');
    }
}
