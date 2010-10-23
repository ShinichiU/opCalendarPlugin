<?php
class opCalendarPluginMigrationVersion2 extends opMigration
{
  public function up($direction)
  {
    $this->removeIndex('holiday', 'month_day_INDEX_idx');
    $this->addColumn('holiday', 'year', 'integer', 4, array('notnull' => false));
    $options = array('fields' => array(
      'year' => array(),
      'month' => array(),
      'day' => array(),
    ));
    $this->addIndex('holiday', 'year_month_day_INDEX_idx', $options);
  }

  public function down($direction)
  {
    $this->removeIndex('holiday', 'year_month_day_INDEX_idx');
    $this->removeColumn('holiday', 'year');
    $options = array('fields' => array(
      'month' => array(),
      'day' => array(),
    ));
    $this->addIndex('holiday', 'month_day_INDEX_idx', $options);
  }
}
