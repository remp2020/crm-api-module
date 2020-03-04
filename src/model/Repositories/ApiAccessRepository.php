<?php

namespace Crm\ApiModule\Repository;

use Crm\ApplicationModule\Repository;
use DateTime;

class ApiAccessRepository extends Repository
{
    protected $tableName = 'api_access';

    final public function add($resource)
    {
        return $this->insert([
            'resource' => $resource,
            'created_at' => new DateTime(),
        ]);
    }

    final public function exists($resource)
    {
        return $this->getTable()->where(['resource' => $resource])->count('*') > 0;
    }

    final public function all()
    {
        return $this->getTable()->order('resource ASC');
    }
}
