<?php

/**
 * PluginScheduleResourceLock form.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: sfDoctrineFormPluginTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
abstract class PluginScheduleResourceLockForm extends BaseScheduleResourceLockForm
{
  private static $resources = null;

  public function setup()
  {
    parent::setup();
    $this->useFields(array('schedule_resource_id'));

    $resources = $this->getResources();

    $options = array(
      'with_delete'  => true,
      'delete_label' => 'リソースを削除する',
      'label'        => false,
      'edit_mode'    => !$this->isNew(),
      'choices'      => $resources,
    );

    $this->setWidget('schedule_resource_id', new opWidgetFormSelectEditable($options));
    $this->validatorSchema['schedule_resource_id'] = new sfValidatorChoice(array('required' => !$this->isNew(), 'choices' => array_keys($resources)));
    if (!$this->isNew())
    {
      $this->setValidator('schedule_resource_id_delete', new sfValidatorBoolean(array('required' => false)));
    }
    $this->widgetSchema->setLabel('schedule_resource_id', false);
  }

  private function getResources()
  {
    $member = sfContext::getInstance()->getUser()->getMember();
    if (null === self::$resources)
    {
      self::$resources = Doctrine::getTable('ScheduleResource')->getResourcesByMember($member);
    }
    $params = $this->isNew() ? array('' => '選択してください') : array();

    foreach (self::$resources as $resource)
    {
      $params[$resource['id']] = $resource['name'];
    }

    return $params;
  }

  public function updateObject($values = null)
  {
    $object = parent::updateObject($values);

    $scheduleResourceLock = $this->getObject();
    $schedule = $scheduleResourceLock->Schedule;
    $start = $schedule->start_date;
    $end = $schedule->end_date;
    if ($schedule->start_time)
    {
      $start .= ' '.$schedule->start_time;
    }
    else
    {
      $start .= ' 00:00:00';
    }

    if ($schedule->end_time)
    {
      $end .= ' '.$schedule->end_time;
    }
    else
    {
      $end .= ' 23:59:59';
    }

    $scheduleResourceLock->setLockStartTime($start);
    $scheduleResourceLock->setLockEndTime($end);

    return $object;
  }
}
