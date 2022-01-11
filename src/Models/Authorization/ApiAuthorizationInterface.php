<?php

namespace Crm\ApiModule\Authorization;

use Nette\Security\Authorizator;

interface ApiAuthorizationInterface
{
    public function authorized($resource = Authorizator::ALL): bool;

    public function getErrorMessage(): ?string;

    public function getAuthorizedData();
}
