<?php

namespace Crm\ApiModule\Authorization;

use Nette\Security\Authorizator;

interface ApiAuthorizationInterface
{
    public function authorized($resource = Authorizator::ALL);

    public function getErrorMessage();

    public function getAuthorizedData();
}
