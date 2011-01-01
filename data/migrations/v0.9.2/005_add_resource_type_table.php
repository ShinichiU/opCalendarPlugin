<?php
class opCalendarPluginMigrationVersion5 extends opMigration
{
  public function up($direction)
  {
    $conn = opDoctrineQuery::chooseConnection(true);
    $sql = 'CREATE TABLE `resource_type` ('
         . ' `id` int(11) NOT NULL AUTO_INCREMENT,'
         . ' `name` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT \'\','
         . ' `description` text COLLATE utf8_bin NOT NULL,'
         . ' PRIMARY KEY (`id`),'
         . ' UNIQUE KEY `name_UNIQUE_idx` (`name`)'
         . ') ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin';
    $conn->execute($sql);
  }

  public function down($direction)
  {
    $conn = opDoctrineQuery::chooseConnection(true);
    $sql = 'DROP TABLE `resource_type`';
    $conn->execute($sql);
  }
}
