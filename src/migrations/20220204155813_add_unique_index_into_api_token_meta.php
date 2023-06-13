<?php

use Phinx\Migration\AbstractMigration;

class AddUniqueIndexIntoApiTokenMeta extends AbstractMigration
{
    public function up()
    {
        $this->table('api_token_meta')
            ->removeIndex(['key'])
            ->addIndex(['api_token_id', 'key'], ['unique' => true])
            ->update();
    }

    public function down()
    {
        // foreign key has to be removed before index
        $this->table('api_token_meta')
            ->dropForeignKey('api_token_id')
            ->update();

        $this->table('api_token_meta')
            ->removeIndex(['api_token_id', 'key'])
            ->addIndex('key')
            ->update();

        $this->table('api_token_meta')
            ->addForeignKey('api_token_id', 'api_tokens', 'id')
            ->update();
    }
}
