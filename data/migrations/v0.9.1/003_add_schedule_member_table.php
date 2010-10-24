<?php
class opCalendarPluginMigrationVersion3 extends opMigration
{
  public function up($direction)
  {
    $columns = array(
      'id' => array(
        'type' => 'integer',
        'length' => '4',
        'autoincrement' => '1',
        'primary' => '1',
      ),
      'member_id' => array(
        'type' => 'integer',
        'length' => '4',
        'notnull' => '1',
      ),
      'schedule_id' => array(
        'type' => 'integer',
        'length' => '4',
        'notnull' => '1',
      )
    );
    $options = array(
      'type'     => 'INNODB',
      'charset'  => 'utf8'
    );
    $this->createTable('schedule_member', $columns, $options);
    $this->createForeignKey('schedule_member', 'schedule_member_schedule_id_schedule_id', array(
      'name' => 'schedule_member_schedule_id_schedule_id',
      'local'         => 'schedule_id',
      'foreign'       => 'id',
      'foreignTable'  => 'schedule',
      'onUpdate'      => '',
      'onDelete'      => 'CASCADE',
    ));
    $this->createForeignKey('schedule_member', 'schedule_member_member_id_member_id', array(
      'name' => 'schedule_member_schedule_id_schedule_id',
      'local'         => 'member_id',
      'foreign'       => 'id',
      'foreignTable'  => 'member',
      'onUpdate'      => '',
      'onDelete'      => 'CASCADE',
    ));
    $this->addIndex('schedule_member', 'member_id_schedule_id_INDEX_idx', array(
      'fields' => array(
        'member_id'   => array(),
        'schedule_id' => array(),
      ),
    ));
    $this->addIndex('schedule_member', 'member_id_idx', array(
      'fields' => array(
        'member_id' => array(),
      ),
    ));
    $this->addIndex('schedule_member', 'schedule_id_idx', array(
      'fields' => array(
        'schedule_id' => array(),
      ),
    ));
  }

  public function postUp()
  {
    $conn = Doctrine_Manager::getInstance()->getConnectionForComponent('Schedule');
    $results = $conn->fetchAll('SELECT id, member_id FROM schedule');
    foreach ($results as $result)
    {
      $conn->update(Doctrine::getTable('ScheduleMember'), array('member_id' => $result['member_id']), array('schedule_id' => $result['id']));
    }
  }

  public function down($direction)
  {
    $this->dropTable('schedule_member');
  }
}
