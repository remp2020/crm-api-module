<?php

namespace Crm\ApiModule\Models\Response;

use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Http\Response;

/**
 * @deprecated use \Nette\Application\Responses\RedirectResponse
 */
class RedirectResponse implements ApiResponseInterface
{
    private int $code = Response::S307_TEMPORARY_REDIRECT;

    private $url;

    public function __construct(string $url)
    {
        $this->url = $url;
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
        header("Location: {$this->url}");
    }
}
