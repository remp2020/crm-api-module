<?php

namespace Crm\ApiModule\Repositories;

use Crm\ApplicationModule\Models\Database\Repository;
use Nette\Database\Table\ActiveRow;

class ApiAccessTokensRepository extends Repository
{
    protected $tableName = 'api_access_tokens';

    final public function assignAccess(ActiveRow $token, ActiveRow $apiAccess)
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
