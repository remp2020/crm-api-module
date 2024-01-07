<?php

namespace Crm\ApiModule\Models\Response;

use Tomaj\NetteApi\Response\ResponseInterface;

interface ApiResponseInterface extends ResponseInterface
{
    /**
     * @deprecated use getCode()
     */
    public function getHttpCode();

    public function getCode(): int;

    /**
     * @deprecated use setCode()
     */
    public function setHttpCode($httpCode);

    public function setCode(int $code);
}
