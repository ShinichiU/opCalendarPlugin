<?php
class opCalendarPluginMigrationVersion10 extends opMigration
{
  public function up($direction)
  {
    Doctrine_Core::getTable('SnsConfig')->createQuery()
      ->delete()
      ->whereIn('value', array('op_calendar_google_data_api_key', 'op_calendar_google_data_api_secret'))
      ->execute();
  }
}
