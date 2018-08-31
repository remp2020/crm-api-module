<?php

use Phinx\Migration\AbstractMigration;

class AddUserSourceAccesses extends AbstractMigration
{
    public function up()
    {
        $sql = <<<SQL
SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';


CREATE TABLE IF NOT EXISTS `user_source_accesses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_accessed_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`,`source`),
  CONSTRAINT `user_source_accesses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- 2018-08-31 07:42:42
SQL;
        $this->execute($sql);
    }

    public function down()
    {
        // TODO: [refactoring] add down migrations for module init migrations
        $this->output->writeln('Down migration is not available.');
    }
}
