<?php

use Phinx\Migration\AbstractMigration;

class IdempotentKeys extends AbstractMigration
{
    public function change()
    {
        $this->table('idempotent_keys')
            ->addColumn('key', 'string')
            ->addColumn('path', 'string')
            ->addColumn('created_at', 'datetime')
            ->addIndex(['key', 'path'], ['unique' => true])
            ->create();
    }
}
