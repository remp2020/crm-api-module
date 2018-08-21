<?php

namespace Crm\ApiModule\Authorization;

use Nette\Security\IAuthorizator;

class NoAuthorization implements ApiAuthorizationInterface
{
    public function authorized($resource = IAuthorizator::ALL)
    {
        return true;
    }

    public function getErrorMessage()
    {
        return false;
    }

    public function getAuthorizedData()
    {
        return [];
    }
}
