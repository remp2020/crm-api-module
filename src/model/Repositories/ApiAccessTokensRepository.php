<?php

namespace Crm\ApiModule\Repository;

use Crm\ApplicationModule\Repository;
use Nette\Database\Table\IRow;

class ApiAccessTokensRepository extends Repository
{
    protected $tableName = 'api_access_tokens';

    public function assignAccess(IRow $token, IRow $apiAccess)
    {
        $row = $this->getTable()->where([
            'api_access_id' => $apiAccess->id,
            'api_token_id' => $token->id,
        ])->fetch();

        if (!$row) {
            $row = $this->insert([
                'api_access_id' => $apiAccess->id,
                'api_token_id' => $token->id,
            ]);
        }
        return $row;
    }
}
