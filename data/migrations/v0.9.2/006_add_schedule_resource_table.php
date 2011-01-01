<?php
class opCalendarPluginMigrationVersion6 extends opMigration
{
  public function up($direction)
  {
    $conn = opDoctrineQuery::chooseConnection(true);
    $sql = 'CREATE TABLE `schedule_resource` ('
         . ' `id` int(11) NOT NULL AUTO_INCREMENT,'
         . ' `name` varchar(64) COLLATE utf8_bin NOT NULL DEFAULT \'\','
         . ' `resource_type_id` int(11) DEFAULT NULL,'
         . ' `resource_limit` int(11) DEFAULT \'1\','
         . ' `description` text COLLATE utf8_bin,'
         . ' `member_id` int(11) DEFAULT NULL,'
         . ' `admin_user_id` int(11) DEFAULT NULL,'
         . ' PRIMARY KEY (`id`),'
         . ' UNIQUE KEY `name_UNIQUE_idx` (`name`),'
         . ' KEY `member_id_idx` (`member_id`),'
         . ' KEY `admin_user_id_idx` (`admin_user_id`),'
         . ' KEY `resource_type_id_idx` (`resource_type_id`),'
         . ' CONSTRAINT `schedule_resource_admin_user_id_admin_user_id` FOREIGN KEY (`admin_user_id`) REFERENCES `admin_user` (`id`) ON DELETE CASCADE,'
         . ' CONSTRAINT `schedule_resource_member_id_member_id` FOREIGN KEY (`member_id`) REFERENCES `member` (`id`) ON DELETE CASCADE,'
         . ' CONSTRAINT `schedule_resource_resource_type_id_resource_type_id` FOREIGN KEY (`resource_type_id`) REFERENCES `resource_type` (`id`) ON DELETE SET NULL'
         . ') ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin';
    $conn->execute($sql);
  }

  public function down($direction)
  {
    $conn = opDoctrineQuery::chooseConnection(true);
    $sql = 'DROP TABLE `schedule_resource`';
    $conn->execute($sql);
  }
}
