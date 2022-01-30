<?php

namespace Crm\ApiModule\Repository;

use Crm\ApplicationModule\Repository;
use Crm\ApplicationModule\Repository\RetentionData;
use DateTime;

class ApiLogsRepository extends Repository
{
    use RetentionData;

    protected $tableName = 'api_logs';

    final public function getLast($limit = 200)
    {
        return $this->getTable()->order('created_at DESC')->limit($limit);
    }

    final public function add($token, $path, $input, $responseCode, $responseTime, $ip, $userAgent)
    {
        return $this->insert([
            'token' => $token,
            'path' => $path,
            'input' => $input,
            'response_code' => $responseCode,
            'response_time' => $responseTime,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'created_at' => new DateTime(),
        ]);
    }
}
