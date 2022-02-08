<?php

use Phinx\Migration\AbstractMigration;

class AddUniqueIndexIntoApiTokenMeta extends AbstractMigration
{
    public function change()
    {
        $this->table('api_token_meta')
            ->removeIndex(['key'])
            ->addIndex(['api_token_id', 'key'], ['unique' => true])
            ->update();
    }
}
