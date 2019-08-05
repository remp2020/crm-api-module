<?php

use Phinx\Migration\AbstractMigration;

class ApiTranslateConfigs extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            update configs set display_name = 'api.config.enable_api_log.name' where name = 'enable_api_log';
            update configs set description = 'api.config.enable_api_log.description' where name = 'enable_api_log';
            
            update configs set display_name = 'api.config.internal_api_token.name' where name = 'internal_api_token';
            update configs set description = 'api.config.internal_api_token.description' where name = 'internal_api_token';
        ");
    }

    public function down()
    {

    }
}
