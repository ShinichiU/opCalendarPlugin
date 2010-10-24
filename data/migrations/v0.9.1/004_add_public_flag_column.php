<?php
class opCalendarPluginMigrationVersion4 extends opMigration
{
  public function up($direction)
  {
    $this->addColumn('schedule', 'public_flag', 'integer', 1, array('notnull' => true, 'default' => 1));
  }

  public function down($direction)
  {
    $this->removeColumn('schedule', 'public_flag');
  }
}
