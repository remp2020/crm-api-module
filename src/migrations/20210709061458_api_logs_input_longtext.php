<?php

use Phinx\Migration\AbstractMigration;

class ApiLogsInputLongtext extends AbstractMigration
{
    public function up()
    {
        // three step column type change to avoid table lock for writes

        // 1. prepare new table (it needs to be separate table to avoid table metadata lock

        $this->execute('CREATE TABLE api_logs_new LIKE api_logs');
        $this->table('api_logs_new')
            ->changeColumn('input', 'text', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_MEDIUM])
            ->update();

        // 2. swap the tables

        $this->table('api_logs')->rename('api_logs_old')->update();
        $this->table('api_logs_new')->rename('api_logs')->update();

        // 3. copy the records to the new table in chunks
        //
        // each query blocks the table for writes, so splitting it to smaller chunks allows the waiting queries
        // to be executed continuosly.

        $count = $this->fetchRow('SELECT COUNT(*) as count from api_logs_old')['count'];
        $offset = 0;

        while ($offset < $count) {
            $sql = <<<SQL
INSERT INTO `api_logs` (`token`, `path`, `response_code`, `response_time`, `ip`, `user_agent`, `created_at`, `input`)
SELECT `token`, `path`, `response_code`, `response_time`, `ip`, `user_agent`, `created_at`, `input`
FROM `api_logs_old`
ORDER BY `api_logs_old`.`id`
LIMIT 10000 OFFSET {$offset}
SQL;
            $this->execute($sql);
            $offset += 10000;
        }

        // 4. get rid of the old table

        $this->table('api_logs_old')
            ->drop()
            ->update();
    }

    public function down()
    {
        $this->execute('CREATE TABLE api_logs_new LIKE api_logs');
        $this->table('api_logs_new')
            ->changeColumn('input', 'text')
            ->update();

        $this->table('api_logs')->rename('api_logs_old')->update();
        $this->table('api_logs_new')->rename('api_logs')->update();

        $count = $this->fetchRow('SELECT COUNT(*) as count from api_logs_old')['count'];
        $offset = 0;

        while ($offset < $count) {
            $sql = <<<SQL
INSERT INTO `api_logs` (`token`, `path`, `response_code`, `response_time`, `ip`, `user_agent`, `created_at`, `input`)
SELECT `token`, `path`, `response_code`, `response_time`, `ip`, `user_agent`, `created_at`, SUBSTR(`input`, 1, 65535)
FROM `api_logs_old`
ORDER BY `api_logs_old`.`id`
LIMIT 10000 OFFSET {$offset}
SQL;
            $this->execute($sql);
            $offset += 10000;
        }

        $this->table('api_logs_old')
            ->drop()
            ->update();
    }
}
