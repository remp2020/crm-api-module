<?php

namespace Crm\ApiModule\Repositories;

use Crm\ApplicationModule\Models\Database\Repository;

class UserSourceAccessesRepository extends Repository
{
    protected $tableName = 'user_source_accesses';

    final public function getLast($limit = 200)
    {
        return $this->getTable()->order('created_at DESC')->limit($limit);
    }

    final public function upsert(int $userId, string $source, \DateTime $lastAccessedDate)
    {
        $row = $this->getTable()
            ->where('user_id', $userId)
            ->where('source', $source)
            ->fetch();

        if ($row) {
            if ($lastAccessedDate > $row->last_accessed_at) {
                $this->update($row, ['last_accessed_at' => $lastAccessedDate]);
                return $row;
            } else {
                return $row;
            }
        } else {
            return $this->insert([
                'user_id' => $userId,
                'source' => $source,
                'last_accessed_at' => $lastAccessedDate,
            ]);
        }
    }

    final public function getByUser($userId)
    {
        return $this->getTable()->where([
            'user_id' => $userId,
        ])->order('last_accessed_at DESC');
    }
}
