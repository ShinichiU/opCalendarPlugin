<?php
class opCalendarPluginMigrationVersion7 extends opMigration
{
  public function up($direction)
  {
    $conn = opDoctrineQuery::chooseConnection(true);
    $sql = 'CREATE TABLE `schedule_resource_lock` ('
         . ' `id` int(11) NOT NULL AUTO_INCREMENT,'
         . ' `schedule_resource_id` int(11) NOT NULL,'
         . ' `schedule_id` int(11) NOT NULL,'
         . ' `lock_start_time` datetime NOT NULL,'
         . ' `lock_end_time` datetime NOT NULL,'
         . ' PRIMARY KEY (`id`),'
         . ' KEY `schedule_resource_id_idx` (`schedule_resource_id`),'
         . ' KEY `schedule_id_idx` (`schedule_id`),'
         . ' KEY `lock_start_time_lock_end_time_idx` (`lock_start_time`,`lock_end_time`),'
         . ' CONSTRAINT `schedule_resource_lock_schedule_id_schedule_id` FOREIGN KEY (`schedule_id`) REFERENCES `schedule` (`id`) ON DELETE CASCADE,'
         . ' CONSTRAINT `schedule_resource_lock_schedule_resource_id_schedule_resource_id` FOREIGN KEY (`schedule_resource_id`) REFERENCES `schedule_resource` (`id`) ON DELETE CASCADE'
         . ') ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8';
    $conn->execute($sql);
  }

  public function down($direction)
  {
    $conn = opDoctrineQuery::chooseConnection(true);
    $sql = 'DROP TABLE `schedule_resource_lock`';
    $conn->execute($sql);
  }
}
