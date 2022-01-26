<?php

namespace Crm\ApiModule\Authorization;

use Crm\ApiModule\Repository\ApiAccessRepository;
use Nette\Security\IAuthorizator;

class Permissions
{
    private $apiAccessRepository;

    public function __construct(ApiAccessRepository $apiAccessRepository)
    {
        $this->apiAccessRepository = $apiAccessRepository;
    }

    public function allowed($token, $resource)
    {
        if ($resource === IAuthorizator::ALL || $resource === IAuthorizator::ALLOW) {
            return true;
        }
        if ($resource === IAuthorizator::DENY) {
            return false;
        }
        return $this->apiAccessRepository->getTable()
            ->where([
                ':api_access_tokens.api_token_id' => $token,
                'resource' => $resource,
            ])->count('*');
    }
}
