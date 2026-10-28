<?php

namespace Crm\ApiModule\Models\Authorization;

use Crm\AdminModule\Helpers\SecuredAdminAccess;
use Crm\UsersModule\Repositories\UsersRepository;
use Nette\Http\FetchSite;
use Nette\Http\IRequest;
use Nette\Security\Authorizator;
use Nette\Security\User;

/**
 * Authorizes API calls made from CRM admin pages using the admin's session.
 */
class AdminLoggedAuthorization implements ApiAuthorizationInterface
{
    private $user;

    private $usersRepository;

    private $securedAdminAccess;

    private $httpRequest;

    private $errorMessage = null;

    public function __construct(
        User $user,
        UsersRepository $usersRepository,
        SecuredAdminAccess $securedAdminAccess,
        IRequest $httpRequest,
    ) {
        $this->user = $user;
        $this->usersRepository = $usersRepository;
        $this->securedAdminAccess = $securedAdminAccess;
        $this->httpRequest = $httpRequest;
    }

    public function authorized($resource = Authorizator::ALL): bool
    {
        if (!$this->user->isLoggedIn()) {
            $this->errorMessage = 'User not logged in';
            return false;
        }

        $userRow = $this->usersRepository->find($this->user->getId());
        if (!$userRow || $userRow->role !== UsersRepository::ROLE_ADMIN) {
            $this->errorMessage = 'User not admin';
            return false;
        }

        // same requirement as AdminPresenter (e.g. SSO login when admin_secure_login_check is enabled)
        if (!$this->securedAdminAccess->isSecure($userRow)) {
            $this->errorMessage = 'Insecure admin login';
            return false;
        }

        // CSRF protection
        if (!$this->httpRequest->isFrom([FetchSite::SameOrigin, FetchSite::None])) {
            $this->errorMessage = 'Cross-origin request';
            return false;
        }

        return true;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getAuthorizedData()
    {
        return [];
    }
}
