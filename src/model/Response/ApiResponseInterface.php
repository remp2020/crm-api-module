<?php

namespace Crm\ApiModule\Response;

use Nette\Application\IResponse;

interface ApiResponseInterface extends IResponse
{
    public function getHttpCode();

    public function setHttpCode($httpCode);
}
