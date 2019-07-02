<?php

namespace Crm\ApiModule\Token;

use Crm\ApiModule\Repository\ApiAccessRepository;
use Crm\ApiModule\Repository\ApiAccessTokensRepository;
use Crm\ApiModule\Repository\ApiTokensRepository;
use Crm\ApplicationModule\Config\Repository\ConfigsRepository;

class InternalToken
{
    const CONFIG_NAME = "internal_api_token";

    private $apiTokensRepository;

    private $apiAccessRepository;

    private $apiAccessTokensRepository;

    private $configsRepository;

    public function __construct(
        ApiTokensRepository $apiTokensRepository,
        ApiAccessRepository $apiAccessRepository,
        ApiAccessTokensRepository $apiAccessTokensRepository,
        ConfigsRepository $configsRepository
    ) {
        $this->apiTokensRepository = $apiTokensRepository;
        $this->apiAccessRepository = $apiAccessRepository;
        $this->apiAccessTokensRepository = $apiAccessTokensRepository;
        $this->configsRepository = $configsRepository;
    }

    /**
     * Give internal token access to all api resources
     * @throws \Exception thrown if internal token is missing in configs or api_tokens tables
     */
    public function addAccessToAllApiResources()
    {
        $apiToken = $this->apiTokensRepository->findBy('token', $this->tokenValue());
        if (!$apiToken) {
            throw new \Exception("Missing internal token in api_tokens table");
        }

        foreach ($this->apiAccessRepository->all() as $apiAccess) {
            $this->apiAccessTokensRepository->assignAccess($apiToken, $apiAccess);
        }
    }

    public function tokenValue(): string
    {
        $tokenConfig = $this->configsRepository->loadByName(self::CONFIG_NAME);
        if (!$tokenConfig) {
            throw new \Exception('Missing ' . self::CONFIG_NAME . ' config value');
        }
        return $tokenConfig->value;
    }
}
