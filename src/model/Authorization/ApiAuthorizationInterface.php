<?php

namespace Crm\ApiModule\Authorization;

use Nette\Security\IAuthorizator;

interface ApiAuthorizationInterface
{
    public function authorized($resource = IAuthorizator::ALL);

    public function getErrorMessage();

    public function getAuthorizedData();
}
