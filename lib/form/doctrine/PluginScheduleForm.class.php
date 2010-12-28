<?php

/**
 * PluginSchedule form.
 *
 * @package    opCalendarPlugin
 * @subpackage form
 * @author     Shinichi Urabe <urabe@tejimaya.com>
 */
abstract class PluginScheduleForm extends BaseScheduleForm
{
  protected
    $dateTime = array();

  public function setup()
  {
    parent::setup();

    $this->generateDateTime();
    $members = opCalendarPluginExtension::getAllowedFriendMember(sfContext::getInstance()->getUser()->getMember());

    $this->setWidget('title', new sfWidgetFormInput());

    $dateObj = new sfWidgetFormI18nDate(array(
      'format' => '%year%年%month%月%day%日',
      'culture' => 'ja_JP',
      'month_format' => 'number',
      'years' => $this->dateTime['years'],
    ));
    $this->setWidget('start_date', $dateObj);
    $this->setWidget('end_date', $dateObj);

    $timeObj = new sfWidgetFormTime(array(
      'with_seconds' => true,
      'format' => '%hour%時%minute%分',
      'minutes' => $this->dateTime['minutes'],
    ));
    $this->setWidget('start_time', $timeObj);
    $this->setWidget('end_time', $timeObj);
    $this->setWidget('public_flag', new sfWidgetFormChoice(array(
      'choices'  => Doctrine::getTable('Schedule')->getPublicFlags(),
      'expanded' => true,
    )));
    $this->setWidget('schedule_member', new sfWidgetFormSelectCheckbox(array(
      'choices'  => $members,
    )));

    $this->setDefault('schedule_member', $this->getDefaultSheduleMembers());

    $this->validatorSchema['title'] = new opValidatorString(array('trim' => true));
    $this->validatorSchema['public_flag'] = new sfValidatorChoice(array(
      'choices' => array_keys(Doctrine::getTable('Schedule')->getPublicFlags()),
    ));
    $this->validatorSchema['schedule_member'] = new sfValidatorChoice(array(
      'choices' => array_keys($members),
      'multiple' => true,
    ));
    $this->validatorSchema->setPostValidator(new sfValidatorCallback(
      array('callback' => array($this, 'validateEndDate')),
      array('invalid' => '終了日時は開始日時より前に設定できません')
    ));
    $this->validatorSchema->setPostValidator(new sfValidatorCallback(
      array('callback' => array($this, 'validateResourceLock')),
      array('invalid' => '予約済みのスケジュールリソースを選択しています')
    ));

    $this->useFields(array('title', 'start_date', 'start_time', 'end_date', 'end_time', 'body', 'public_flag', 'schedule_member'));

    if (!$this->isNew())
    {
      $scheduleResourceLocks = $this->getObject()->ScheduleResourceLocks;
    }

    $max = (int)sfConfig::get('app_schedule_resource_list_max', 5);
    for ($i = 1; $i <= $max; $i++)
    {
      $key = 'schedule_resource_lock_'.$i;

      if (isset($scheduleResourceLocks[$i - 1]))
      {
        $scheduleResourceLock = $scheduleResourceLocks[$i - 1];
      }
      else
      {
        $scheduleResourceLock = new ScheduleResourceLock();
        $scheduleResourceLock->setSchedule($this->getObject());
      }

      $scheduleResourceLockForm = new ScheduleResourceLockForm($scheduleResourceLock);
      $scheduleResourceLockForm->getWidgetSchema()->setFormFormatterName('list');
      $this->embedForm($key, $scheduleResourceLockForm, '<ul id="schedule_resource_lock_'.$key.'">%content%</ul>');
    }
  }

  private function generateDateTime()
  {
    $startYear = (int)date('Y');
    $endYear = $startYear + 1;
    $years = range($startYear, $endYear);
    $this->dateTime['years'] = array_combine($years, $years);

    $minutes = array(0, 15, 30, 45);
    $this->dateTime['minutes'] = array_combine($minutes, $minutes);
  }

  public function validateEndDate(sfValidatorBase $validator, $values)
  {
    $start_datetime = $values['start_date'];
    $end_datetime   = $values['end_date'];
    if (isset($values['start_time']) && isset($values['end_time']))
    {
      $start_datetime .= ' '.$values['start_time'];
      $end_datetime   .= ' '.$values['end_time'];
    }
    $start = strtotime($start_datetime);
    $end   = strtotime($end_datetime);

    if ($start > $end)
    {
      throw new sfValidatorError($validator, 'invalid');
    }

    return $values;
  }

  public function validateResourceLock(sfValidatorBase $validator, $values)
  {
    foreach (array_keys($this->embeddedForms) as $key)
    {
      if ($schedule_resource_id = $values[$key]['schedule_resource_id'])
      {
        if (!Doctrine::getTable('ScheduleResourceLock')->isValidScheduleResource($schedule_resource_id, $values['start_date'], $values['end_date'], $values['start_time'], $values['end_time'], $this->getObject()->id))
        {
          throw new sfValidatorError($validator, 'invalid');
        }
      }
    }

    return $values;
  }

  private function getDefaultSheduleMembers()
  {
    if ($this->isNew())
    {
      return sfContext::getInstance()->getUser()->getMemberId();
    }
    $scheduleMemberIds = Doctrine::getTable('ScheduleMember')->getMemberIdsBySchedule($this->getObject());
    $results = array();
    foreach ($scheduleMemberIds as $id)
    {
      $results[] = $id;
    }

    return $results;
  }

  public function updateObject($values = null)
  {
    $object = parent::updateObject($values);

    foreach ($this->embeddedForms as $key => $form)
    {
      $values = $this->getValue($key);
      if (!($form->getObject() && $values['schedule_resource_id']))
      {
        unset($this->embeddedForms[$key]);
      }
    }

    $scheduleMembers = $this->getObject()->getScheduleMembers();
    foreach ($scheduleMembers as $scheduleMember)
    {
      $scheduleMember->delete();
      $scheduleMember->free();
      unset($scheduleMember);
    }

    $formScheduleMembers = $this->getValue('schedule_member');
    foreach ($formScheduleMembers as $formScheduleMember)
    {
      $scheduleMember = new ScheduleMember();
      $scheduleMember->setSchedule($object);
      $scheduleMember->setMemberId($formScheduleMember);
      $scheduleMember->save();
    }

    return $object;
  }
}
