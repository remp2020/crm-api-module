<?php

namespace Crm\ApiModule\Models\Authorization;

use Crm\ApiModule\Repository\ApiAccessRepository;
use Nette\Security\Authorizator;

class Permissions
{
    private $apiAccessRepository;

    public function __construct(ApiAccessRepository $apiAccessRepository)
    {
        $this->apiAccessRepository = $apiAccessRepository;
    }

    public function allowed($token, $resource)
    {
        if ($resource === Authorizator::ALL || $resource === Authorizator::ALLOW) {
            return true;
        }
        if ($resource === Authorizator::DENY) {
            return false;
        }
        return $this->apiAccessRepository->getTable()
            ->where([
                ':api_access_tokens.api_token_id' => $token,
                'resource' => $resource,
            ])->count('*');
    }
}
