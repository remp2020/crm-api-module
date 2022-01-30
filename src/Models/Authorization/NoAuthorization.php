<?php

namespace Crm\ApiModule\Authorization;

use Nette\Security\Authorizator;

class NoAuthorization implements ApiAuthorizationInterface
{
    public function authorized($resource = Authorizator::ALL)
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
