<?php

namespace Crm\ApiModule\Repository;

use Crm\ApplicationModule\Repository;

class UserSourceAccessesRepository extends Repository
{
    protected $tableName = 'user_source_accesses';

    public function getLast($limit = 200)
    {
        return $this->getTable()->order('created_at DESC')->limit($limit);
    }

    /**
     * @param $userId
     * @param $source
     * @param \DateTime $lastAccessedDate
     */
    public function upsert($userId, $source, $lastAccessedDate)
    {
        $datetime = $lastAccessedDate->format('Y-m-d H:i:s');

        $this->getDatabase()->query(
            <<<SQL
INSERT INTO {$this->tableName} (`user_id`, `source`, `last_accessed_at`)
VALUES ({$userId}, '{$source}', '{$datetime}')
ON DUPLICATE KEY UPDATE `last_accessed_at` = CASE
  WHEN VALUES(`last_accessed_at`) > `last_accessed_at` THEN VALUES(`last_accessed_at`) ELSE `last_accessed_at`
END
SQL
        );
    }

    public function getByUser($userId)
    {
        return $this->getTable()->where([
            'user_id' => $userId,
        ])->order('last_accessed_at DESC');
    }
}
