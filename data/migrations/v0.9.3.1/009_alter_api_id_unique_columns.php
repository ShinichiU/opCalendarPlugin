<?php
class opCalendarPluginMigrationVersion9 extends opMigration
{
  public function up($direction)
  {
    $this->changeColumn('schedule', 'api_id_unique', 'string', 64);
  }

  public function down($direction)
  {
    $this->changeColumn('schedule', 'api_id_unique', 'string', 32);
  }
}
