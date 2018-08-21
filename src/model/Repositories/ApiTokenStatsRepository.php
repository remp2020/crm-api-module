<?php

namespace Crm\ApiModule\Repository;

use Crm\ApplicationModule\Repository;
use Nette\Database\Context;

class ApiTokenStatsRepository extends Repository
{
    protected $tableName = 'api_token_stats';

    private $tokenRepository;

    public function __construct(Context $database, ApiTokensRepository $tokenRepository)
    {
        parent::__construct($database);
        $this->tokenRepository = $tokenRepository;
    }

    public function updateStats($token)
    {
        $tokenId = $this->tokenRepository->findToken($token);
        if ($tokenId) {
            $now = date('Y-m-d H:i:s');
            $this->database->query("INSERT INTO {$this->tableName} (token_id, calls, last_call) VALUES ('$tokenId',1,'$now') ON DUPLICATE KEY UPDATE calls = calls+1, last_call='$now'");
        }
    }
}
