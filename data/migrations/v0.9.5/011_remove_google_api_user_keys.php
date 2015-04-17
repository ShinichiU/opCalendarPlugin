<?php
class opCalendarPluginMigrationVersion11 extends opMigration
{
  public function up($direction)
  {
    Doctrine_Core::getTable('MemberConfig')->createQuery()
      ->delete()
      ->whereIn('name', array(
        'google_cron_update',
        'google_cron_update_params',
        'google_calendar_oauth_token',
        'google_calendar_oauth_token_secret',
      ))
      ->execute();
  }
}
