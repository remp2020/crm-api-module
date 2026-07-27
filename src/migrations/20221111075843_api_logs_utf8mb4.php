<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ApiLogsUtf8mb4 extends AbstractMigration
{
    public function up(): void
    {
        // column type change done in steps to avoid locking the table for writes

        // 1. prepare new table (a separate table avoids the metadata lock)
        $this->execute('CREATE TABLE api_logs_new LIKE api_logs');
        $this->execute("ALTER TABLE api_logs_new ROW_FORMAT=DYNAMIC");
        $this->execute("ALTER TABLE api_logs_new CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        $this->table('api_logs_new')
            ->changeColumn('input', 'text', [
                'limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_MEDIUM,
                'null' => false,
                'collation' => 'utf8mb4_unicode_ci',
            ])
            ->changeColumn('token', 'string', [
                'collation' => 'utf8mb4_unicode_ci',
            ])
            ->changeColumn('path', 'string', [
                'collation' => 'utf8mb4_unicode_ci',
            ])
            ->changeColumn('ip', 'string', [
                'collation' => 'utf8mb4_unicode_ci',
            ])
            ->changeColumn('user_agent', 'string', [
                'limit' => 2000, // based on the ExpandUserAgentColumn in payments-module
                'collation' => 'utf8mb4_unicode_ci',
            ])
            ->update();

        // 2. swap the tables

        $archiveTable = 'api_logs_' . time();

        $this->table('api_logs')->rename($archiveTable)->update();
        $this->table('api_logs_new')->rename('api_logs')->update();

        $allowedDataMigration = filter_var($_ENV['CRM_ALLOW_API_LOGS_DATA_MIGRATION'] ?? null, FILTER_VALIDATE_BOOLEAN);
        $count = (int)$this->fetchRow("SELECT COUNT(*) as count from {$archiveTable}")['count'];
        if ($allowedDataMigration) {
            // 3. copy the records to the new table in chunks
            //
            // each query blocks the table for writes, so splitting it to smaller chunks allows the waiting queries
            // to be executed continuously.
            $offset = 0;

            while ($offset < $count) {
                $sql = <<<SQL
                    INSERT INTO `api_logs` (`token`, `path`, `response_code`, `response_time`, `ip`, `user_agent`, `created_at`, `input`)
                    SELECT `token`, `path`, `response_code`, `response_time`, `ip`, `user_agent`, `created_at`, `input`
                    FROM `{$archiveTable}`
                    ORDER BY `{$archiveTable}`.`id`
                    LIMIT 10000 OFFSET {$offset}
                    SQL;
                $this->execute($sql);
                $offset += 10000;
            }
        }

        // 4. drop the old table (an empty archive has nothing to preserve)
        if ($allowedDataMigration || $count === 0) {
            $this->table($archiveTable)
                ->drop()
                ->update();
        }
    }

    public function down(): void
    {
        // column type change done in steps to avoid locking the table for writes

        // 1. prepare new table (a separate table avoids the metadata lock)
        $this->execute('CREATE TABLE api_logs_new LIKE api_logs');
        $this->execute("ALTER TABLE api_logs_new CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci");

        $this->table('api_logs_new')
            ->changeColumn('input', 'text', [
                'limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_MEDIUM,
                'null' => false,
                'collation' => 'utf8_general_ci',
            ])
            ->changeColumn('token', 'string', [
                'collation' => 'utf8_general_ci',
            ])
            ->changeColumn('path', 'string', [
                'collation' => 'utf8_general_ci',
            ])
            ->changeColumn('ip', 'string', [
                'collation' => 'utf8_general_ci',
            ])
            ->changeColumn('user_agent', 'string', [
                'collation' => 'utf8_general_ci',
            ])
            ->update();

        // 2. swap the tables
        $archiveTable = 'api_logs_' . time();

        $this->table('api_logs')->rename($archiveTable)->update();
        $this->table('api_logs_new')->rename('api_logs')->update();

        $allowedDataMigration = filter_var($_ENV['CRM_ALLOW_API_LOGS_DATA_MIGRATION'] ?? null, FILTER_VALIDATE_BOOLEAN);
        $count = (int)$this->fetchRow("SELECT COUNT(*) as count from {$archiveTable}")['count'];
        if ($allowedDataMigration) {
            // 3. copy the records to the new table in chunks
            //
            // each query blocks the table for writes, so splitting it to smaller chunks allows the waiting queries
            // to be executed continuously.
            $offset = 0;

            while ($offset < $count) {
                $sql = <<<SQL
                    INSERT INTO `api_logs` (`token`, `path`, `response_code`, `response_time`, `ip`, `user_agent`, `created_at`, `input`)
                    SELECT `token`, `path`, `response_code`, `response_time`, `ip`, `user_agent`, `created_at`, `input`
                    FROM `{$archiveTable}`
                    ORDER BY `{$archiveTable}`.`id`
                    LIMIT 10000 OFFSET {$offset}
                    SQL;
                $this->execute($sql);
                $offset += 10000;
            }
        }

        // 4. drop the old table (an empty archive has nothing to preserve)
        if ($allowedDataMigration || $count === 0) {
            $this->table($archiveTable)
                ->drop()
                ->update();
        }
    }
}
