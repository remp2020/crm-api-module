<?php

namespace Crm\ApiModule\Models\Response;

use Crm\ApiModule\Response\ApiResponseInterface;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * @deprecated use \Tomaj\NetteApi\Response\XmlApiResponse or create copy of this class in your module if you need it. (Internal note: Move this class into MobiletechModule, which is only CRM module that uses it.)
 */
class XmlResponse implements ApiResponseInterface
{
    protected int $code;

    protected $rootElement;

    protected $rootElementAttributes;

    protected $payload;

    public function __construct(array $payload, string $rootElement, array $rootElementAttributes = [])
    {
        $this->payload = $payload;
        $this->rootElement = $rootElement;
        $this->rootElementAttributes = $rootElementAttributes;
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

    public function send(IRequest $httpRequest, IResponse $httpResponse): void
    {
        $httpResponse->setContentType('application/xml', 'utf-8');
        echo ArrayToXml::convert($this->payload, [
            'rootElementName' => $this->rootElement,
            '_attributes' => $this->rootElementAttributes,
        ], true, 'UTF-8');
    }
}
