<?php

namespace Crm\ApiModule\Repository;

use Crm\ApplicationModule\Repository;
use DateTime;
use Nette\Database\Table\IRow;

class ApiTokensRepository extends Repository
{
    protected $tableName = 'api_tokens';

    public function all()
    {
        return $this->getTable()->order('created_at DESC');
    }

    public function generate($name, $ipRestrictions = '*', $active = true)
    {
        $token = md5(rand(0, 100000) . 'asdihasf' . time() . time() . rand(1320, 1240930925));
        return $this->insert([
            'name' => $name,
            'token' => $token,
            'ip_restrictions' => $ipRestrictions,
            'created_at' => new DateTime(),
            'updated_at' => new DateTime(),
            'active' => $active,
        ]);
    }

    public function update(IRow &$row, $data)
    {
        $data['updated_at'] = new DateTime();
        return parent::update($row, $data);
    }

    public function findToken($token)
    {
        $tokenRow = $this->getTable()->where('token', $token)->fetch();
        if (!$tokenRow) {
            return false;
        }
        return $tokenRow;
    }
}
