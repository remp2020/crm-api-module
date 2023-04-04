<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateNewApiLogsTable extends AbstractMigration
{
    public function up(): void
    {
        $autologinTokensRowCount = $this->query('SELECT 1 FROM api_logs LIMIT 1;')->fetch();
        if ($autologinTokensRowCount === false) {
            $this->table('api_logs')
                ->changeColumn('id', 'biginteger', ['identity' => true])
                ->save();
        } else {
            $this->query("
                CREATE TABLE api_logs_v2 LIKE api_logs;
            ");

            $this->table('api_logs_v2')
                ->changeColumn('id', 'biginteger', ['identity' => true])
                ->save();
        }
    }

    public function down()
    {
        $this->output->writeln('Down migration is not available.');
    }
}
