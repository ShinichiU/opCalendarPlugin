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
  public function setup()
  {
    parent::setup();

    $resources = $this->getResources();

    $this->setWidget('schedule_resource_id', new sfWidgetFormSelect(array('choices' => $resources)));
    $this->validatorSchema['schedule_resource_id'] = new sfValidatorChoice(array('required' => false, 'choices' => array_keys($resources)));
    $this->widgetSchema->setLabel('schedule_resource_id', '&lrm;');
    $this->useFields(array('schedule_resource_id'));
  }

  private function getResources()
  {
    $member = sfContext::getInstance()->getUser()->getMember();
    $resources = Doctrine::getTable('ScheduleResource')->getResourcesByMember($member);
    $params = array(null => '選択してください');

    foreach ($resources as $resource)
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
