<?php
class opCalendarPluginMigrationVersion9 extends opMigration
{
  public function up($direction)
  {
    $conn = opDoctrineQuery::chooseConnection(true);
    $sql  = 'CREATE TABLE `schedule_group_member` ('
          . ' `id` int(11) NOT NULL AUTO_INCREMENT,'
          . ' `schedule_group_id` int(11) NOT NULL,'
          . ' `member_id` int(11) NOT NULL,'
          . ' PRIMARY KEY (`id`),'
          . ' KEY `schedule_group_id_member_id_idx` (`schedule_group_id`,`member_id`),'
          . ' KEY `member_id_idx` (`member_id`),'
          . ' KEY `schedule_group_id_idx` (`schedule_group_id`),'
          . ' CONSTRAINT `schedule_group_member_member_id_member_id` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE,'
          . ' CONSTRAINT `schedule_group_member_schedule_group_id_schedule_group_id` FOREIGN KEY (`schedule_group_id`) REFERENCES `schedule_group` (`id`) ON DELETE CASCADE'
          . ') ENGINE=InnoDB DEFAULT CHARSET=utf8';
    $conn->execute($sql);
  }

  public function down($direction)
  {
    $conn = opDoctrineQuery::chooseConnection(true);
    $sql = 'DROP TABLE `schedule_group_member`';
    $conn->execute($sql);
  }
}
