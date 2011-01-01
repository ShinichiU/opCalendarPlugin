<?php
class opCalendarPluginMigrationVersion8 extends opMigration
{
  public function up($direction)
  {
    $conn = opDoctrineQuery::chooseConnection(true);
    $sql = 'CREATE TABLE `schedule_group` ('
         . ' `id` int(11) NOT NULL AUTO_INCREMENT,'
         . ' `name` varchar(64) NOT NULL DEFAULT \'\','
         . ' `prefix` varchar(64) NOT NULL DEFAULT \'\','
         . ' `member_id` int(11) NOT NULL,'
         . ' PRIMARY KEY (`id`),'
         . ' KEY `member_id_idx` (`member_id`),'
         . ' CONSTRAINT `schedule_group_member_id_member_id` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE'
         . ') ENGINE=InnoDB DEFAULT CHARSET=utf8';
    $conn->execute($sql);
  }

  public function down($direction)
  {
    $conn = opDoctrineQuery::chooseConnection(true);
    $sql = 'DROP TABLE `schedule_group`';
    $conn->execute($sql);
  }
}
