<?php

namespace Crm\ApiModule\Repository;

use Crm\ApplicationModule\Repository;
use Nette\Database\Table\IRow;
use Nette\Utils\DateTime;

class ApiTokenMetaRepository extends Repository
{
    protected $tableName = 'api_token_meta';

    final public function upsert(IRow $apiTokenRow, string $key, string $value)
    {
        $data = [
            'key' => $key,
            'value' => $value,
            'updated_at' => new DateTime(),
        ];

        $apiTokenMetaRow = $this->findByApiTokenAndKey($apiTokenRow, $key);
        if ($apiTokenMetaRow) {
            return $this->update($apiTokenMetaRow, $data);
        }

        $data['api_token_id'] = $apiTokenRow->id;
        $data['created_at'] = new DateTime();

        return $this->insert($data);
    }

    final public function findByApiTokenAndKey(IRow $apiTokenRow, string $key)
    {
        return $apiTokenRow->related('api_token_meta')
            ->where('key = ?', $key)
            ->fetch();
    }

    final public function findByApiToken(IRow $apiTokenRow)
    {
        return $apiTokenRow->related('api_token_meta')
            ->fetchAll();
    }

    final public function findByMeta(string $key, string $value)
    {
        return $this->getTable()
            ->where('key = ?', $key)
            ->where('value = ?', $value)
            ->fetch();
    }

    final public function remove(IRow $apiTokenRow, string $key): int
    {
        return $this->getTable()->where([
            'api_token_id' => $apiTokenRow->id,
            'key' => $key,
        ])->delete();
    }
}
