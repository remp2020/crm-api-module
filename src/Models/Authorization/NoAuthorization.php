<?php

namespace Crm\ApiModule\Models\Authorization;

use Nette\Security\Authorizator;

class NoAuthorization implements ApiAuthorizationInterface
{
    public function authorized($resource = Authorizator::ALL): bool
    {
        return true;
    }

    public function getErrorMessage(): ?string
    {
        return null;
    }

    public function getAuthorizedData()
    {
        return [];
    }
}
