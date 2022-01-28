<?php

namespace Crm\ApiModule\Response;

use Nette\Application\Response;

interface ApiResponseInterface extends Response
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
