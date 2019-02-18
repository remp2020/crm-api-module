<?php

namespace Crm\ApiModule\Authorization;

use Crm\UsersModule\Repository\AccessTokensRepository;
use Crm\UsersModule\Repository\UsersRepository;
use Nette\Security\IAuthorizator;

class AdminLoggedAuthorization implements ApiAuthorizationInterface
{
    private $accessTokensRepository;

    private $usersRepository;

    private $errorMessage = false;

    protected $authorizedData = [];

    public function __construct(
        AccessTokensRepository $accessTokensRepository,
        UsersRepository $usersRepository
    ) {
        $this->accessTokensRepository = $accessTokensRepository;
        $this->usersRepository = $usersRepository;
    }

    public function authorized($resource = IAuthorizator::ALL)
    {
        $tokenParser = new TokenParser();
        if (!$tokenParser->isOk()) {
            $this->errorMessage = $tokenParser->errorMessage();
            return false;
        }

        $token = $this->accessTokensRepository->loadToken($tokenParser->getToken());
        if (!$token) {
            $this->errorMessage = "Token doesn't exists";
            return false;
        }

        if (!$this->usersRepository->isRole($token->user_id, UsersRepository::ROLE_ADMIN)) {
            $this->errorMessage = 'User not admin';
            return false;
        }

        $this->authorizedData['token'] = $token;
        return true;
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
