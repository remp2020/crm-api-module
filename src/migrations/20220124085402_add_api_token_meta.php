<?php

use Phinx\Migration\AbstractMigration;

class AddApiTokenMeta extends AbstractMigration
{
    public function change()
    {
        $this->table('api_token_meta')
            ->addColumn('api_token_id', 'integer', ['null' => false])
            ->addColumn('key', 'string', ['null' => false])
            ->addColumn('value','string', ['null' => false])
            ->addColumn('created_at','datetime', ['null' => false])
            ->addColumn('updated_at', 'datetime', ['null' => false])
            ->addForeignKey('api_token_id', 'api_tokens', 'id')
            ->addIndex('key')
            ->addIndex('value')
            ->create();
    }
}
