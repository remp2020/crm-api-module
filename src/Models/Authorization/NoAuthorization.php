<?php

namespace Crm\ApiModule\Authorization;

use Nette\Security\Authorizator;

class NoAuthorization implements ApiAuthorizationInterface
{
    public function authorized($resource = Authorizator::ALL): bool
    {
        return true;
    }

    public function getErrorMessage(): ?string
    {
        return false;
    }

    public function getAuthorizedData()
    {
        return [];
    }
}
