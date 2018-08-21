<?php

namespace Crm\ApiModule\Authorization;

use Nette\Security\IAuthorizator;
use Nette\Security\User;

class AdminLoggedAuthorization implements ApiAuthorizationInterface
{
    /** @var User  */
    private $user;

    private $errorMessage = false;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function authorized($resource = IAuthorizator::ALL)
    {
        if ($this->user->isLoggedIn()) {
            return true;
        }

        $this->errorMessage = 'User not logged';
        return false;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function getAuthorizedData()
    {
        return [];
    }
}
