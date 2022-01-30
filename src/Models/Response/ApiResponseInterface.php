<?php

namespace Crm\ApiModule\Response;

use Nette\Application\Response;

interface ApiResponseInterface extends Response
{
    public function getHttpCode();

    public function setHttpCode($httpCode);
}
