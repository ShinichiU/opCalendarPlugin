<?php
class opCalendarPluginMigrationVersion8 extends opMigration
{
  public function up($direction)
  {
    $this->addColumn('schedule', 'api_flag', 'integer', 1, array('notnull' => false));
    $this->addColumn('schedule', 'api_id_unique', 'string', 32, array('notnull' => false));
    $this->addColumn('schedule', 'api_etag', 'string', 32, array('notnull' => false));
    $options = array('fields' => array(
      'api_flag' => array(),
      'api_id_unique' => array(),
    ));
    $this->addIndex('schedule', 'api_flag_api_id_unique_INDEX_idx', $options);
  }

  public function down($direction)
  {
    $this->removeIndex('schedule', 'api_flag_api_id_unique_INDEX_idx');
    $this->removeColumn('schedule', 'api_flag');
    $this->removeColumn('schedule', 'api_id_unique');
    $this->removeColumn('schedule', 'api_etag');
  }
}
