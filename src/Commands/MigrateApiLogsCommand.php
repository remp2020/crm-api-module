<?php

namespace Crm\ApiModule\Commands;

use Crm\ApiModule\Repositories\ApiLogsRepository;
use Crm\ApplicationModule\Application\EnvironmentConfig;
use Crm\ApplicationModule\Models\Redis\RedisClientFactory;
use Crm\ApplicationModule\Models\Redis\RedisClientTrait;
use Nette\Database\Explorer;
use Nette\Utils\DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateApiLogsCommand extends Command
{
    use RedisClientTrait;

    public const API_LOGS_MIGRATION_RUNNING = 'api_logs_migration_running';

    public const COMMAND_NAME = "api:convert_api_logs_to_bigint";

    public function __construct(
        private Explorer $database,
        private ApiLogsRepository $apiLogsRepository,
        private EnvironmentConfig $environmentConfig,
        RedisClientFactory $redisClientFactory,
    ) {
        parent::__construct();

        $this->redisClientFactory = $redisClientFactory;
    }

    protected function configure(): void
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Migrate api logs data to new table.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('STARTING `api_logs` TABLE DATA MIGRATION');
        $output->writeln('');

        $apiLogsRepositoryTableName = $this->apiLogsRepository->getTable()->getName();
        $apiLogsRepositoryV2TableName = $this->apiLogsRepository->getNewTable()->getName();

        // Set migration running/start time flag in redis
        $migrationStartTime = new DateTime();
        if ($this->redis()->exists(self::API_LOGS_MIGRATION_RUNNING)) {
            $migrationStartTime = new DateTime($this->redis()->get(self::API_LOGS_MIGRATION_RUNNING));
        } else {
            $this->redis()->set(self::API_LOGS_MIGRATION_RUNNING, $migrationStartTime);
        }

        $this->database->query("
            SET FOREIGN_KEY_CHECKS=0;
            SET UNIQUE_CHECKS=0;
        ");

        // Paging LOOP
        $pageSize = 10000;
        while (true) {
            $lastMigratedId = $this->database
                ->query("SELECT id FROM `{$apiLogsRepositoryV2TableName}` WHERE created_at <= ? ORDER BY id DESC LIMIT 1", $migrationStartTime)
                ->fetch()
                ?->id ?? 0;

            $maxId = $this->database
                ->query("SELECT id FROM `{$apiLogsRepositoryTableName}` WHERE created_at <= ? ORDER BY id DESC LIMIT 1", $migrationStartTime)
                ->fetch()
                ?->id ?? 0;

            if ($maxId === 0 || $lastMigratedId === $maxId) {
                break;
            }

            $this->database->query("
                INSERT IGNORE INTO `{$apiLogsRepositoryV2TableName}` (`id`, `token`, `path`, `input`, `response_code`, `response_time`, `ip`, `user_agent`, `created_at`)
                SELECT `id`, `token`, `path`, `input`, `response_code`, `response_time`, `ip`, `user_agent`, `created_at`
                FROM `{$apiLogsRepositoryTableName}`
                WHERE id > {$lastMigratedId}
                ORDER BY id ASC
                LIMIT {$pageSize}
            ");

            $remaining = $maxId-$lastMigratedId;
            $output->write("\r\e[0KMIGRATED IDs: {$lastMigratedId} / {$maxId} (REMAINING: {$remaining})");
        }

        $output->writeln('');
        $output->writeln('DATA MIGRATED');
        $output->writeln('');
        $output->writeln('UPDATING ROWS DIFFERENCES AND INSERTING MISSING ROWS');

        $this->fixTableDifferences(
            $apiLogsRepositoryTableName,
            $apiLogsRepositoryV2TableName,
            $migrationStartTime
        );

        $output->writeln('');
        $output->writeln('SETUPING AUTO_INCREMENT');

        // Sat AUTO_INCREMENT for new tables to old table values
        $dbName = $this->environmentConfig->get('DB_NAME');
        $this->database->query("
            SELECT MAX(id)+10000 INTO @AutoInc FROM {$apiLogsRepositoryTableName};

            SET @s:=CONCAT('ALTER TABLE `{$dbName}`.`{$apiLogsRepositoryV2TableName}` AUTO_INCREMENT=', @AutoInc);
            PREPARE stmt FROM @s;
            EXECUTE stmt;
            DEALLOCATE PREPARE stmt;
        ");

        $output->writeln('');
        $output->writeln('RENAMING TABLES');

        // Rename tables
        $this->database->query("
            ANALYZE TABLE {$apiLogsRepositoryV2TableName};
            RENAME TABLE {$apiLogsRepositoryTableName} TO {$apiLogsRepositoryTableName}_old,
            {$apiLogsRepositoryV2TableName} TO {$apiLogsRepositoryTableName};
        ");

        $output->writeln('');
        $output->writeln('UPDATING ROWS DIFFERENCES AND INSERTING MISSING ROWS');

        $this->fixTableDifferences(
            $apiLogsRepositoryTableName . '_old',
            $apiLogsRepositoryTableName,
            $migrationStartTime
        );

        $this->database->query("
            SET FOREIGN_KEY_CHECKS=1;
            SET UNIQUE_CHECKS=1;
        ");

        // Remove migration running flag in redis
        $this->redis()->del(self::API_LOGS_MIGRATION_RUNNING);

        $output->writeln('');
        $output->writeln('DATA MIGRATED SUCCESSFULLY');
        return Command::SUCCESS;
    }

    public function fixTableDifferences(
        string $fromTable,
        string $toTable,
        DateTime $updatedAfter
    ) {
        $missingIds = $this->database->query("
            SELECT `id` FROM `{$fromTable}`
            WHERE created_at > ?
            AND `id` NOT IN (
                SELECT `id` FROM `{$toTable}` WHERE created_at > ?
            )
        ", $updatedAfter, $updatedAfter)->fetchFields();

        if ($missingIds) {
            $this->database->query("
                INSERT IGNORE INTO `{$toTable}` (`id`, `token`, `path`, `input`, `response_code`, `response_time`, `ip`, `user_agent`, `created_at`)
                SELECT `id`, `token`, `path`, `input`, `response_code`, `response_time`, `ip`, `user_agent`, `created_at`
                FROM `{$fromTable}`
                WHERE `id` IN ?
            ", $missingIds);
        }
    }
}
