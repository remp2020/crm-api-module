<?php

namespace Crm\ApiModule\Repositories;

use Crm\ApplicationModule\Repository;
use Nette\Utils\DateTime;

class IdempotentKeysRepository extends Repository
{
    protected $tableName = 'idempotent_keys';

    final public function add(string $path, string $key)
    {
        return $this->getTable()->insert([
            'path' => $path,
            'key' => $key,
            'created_at' => new DateTime(),
        ]);
    }

    final public function findKey(string $path, string $key)
    {
        return $this->getTable()->where(['path' => $path, 'key' => $key])->limit(1)->fetch();
    }
}
