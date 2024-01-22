<?php

namespace Crm\ApiModule\Repositories;

use Crm\ApplicationModule\Models\Database\Repository;
use Nette\Database\Explorer;

class ApiTokenStatsRepository extends Repository
{
    protected $tableName = 'api_token_stats';

    private $tokenRepository;

    public function __construct(Explorer $database, ApiTokensRepository $tokenRepository)
    {
        parent::__construct($database);
        $this->tokenRepository = $tokenRepository;
    }

    final public function updateStats($token)
    {
        $tokenId = $this->tokenRepository->findToken($token);
        if ($tokenId) {
            $now = date('Y-m-d H:i:s');
            $this->database->query("INSERT INTO {$this->tableName} (token_id, calls, last_call) VALUES ('$tokenId',1,'$now') ON DUPLICATE KEY UPDATE calls = calls+1, last_call='$now'");
        }
    }
}
