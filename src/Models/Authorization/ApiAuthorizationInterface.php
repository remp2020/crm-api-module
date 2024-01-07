<?php

namespace Crm\ApiModule\Models\Authorization;

use Nette\Security\Authorizator;
use Tomaj\NetteApi\Authorization\ApiAuthorizationInterface as TomajApiAuthorizationInterface;

interface ApiAuthorizationInterface extends TomajApiAuthorizationInterface
{
    public function authorized($resource = Authorizator::ALL): bool;

    public function getAuthorizedData();
}
