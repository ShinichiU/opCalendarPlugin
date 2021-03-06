<?php

/**
 * PluginSchedule
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    opCalendarPlugin
 * @subpackage model
 * @author     Shinichi Urabe <urabe@tejimaya.com>
 */
abstract class PluginSchedule extends BaseSchedule
{
  public function getPublicFlagLabel()
  {
    $publicFlags = $this->getTable()->getPublicFlags();

    return $publicFlags[$this->public_flag];
  }

  public function isShowable($memberId)
  {
    if (ScheduleTable::PUBLIC_FLAG_SNS == $this->public_flag)
    {
      return true;
    }

    return $this->isEditable($memberId) || $this->isScheduleMember($memberId);
  }

  public function isScheduleMember($memberId)
  {
    $scheduleMemberIds = Doctrine::getTable('ScheduleMember')->getMemberIdsBySchedule($this);
    foreach ($scheduleMemberIds as $scheduleMemberId)
    {
      if ($scheduleMemberId === $memberId)
      {
        return true;
      }
    }

    return false;
  }

  public function isEditable($memberId)
  {
    return $this->getMemberId() === $memberId;
  }

  public function preDelete($event)
  {
    if (!$this->isSyncable($opCalendarOAuth = opCalendarOAuth::getInstance()))
    {
      return false;
    }

    $opCalendarOAuth->getCalendar($this->getMember())
      ->events
      ->delete($opCalendarOAuth->getPrimaryId($this->getMember()), $this->getApiIdUnique());
  }

  public function preSave($event)
  {
    if (!$this->isSyncable($opCalendarOAuth = opCalendarOAuth::getInstance()))
    {
      return false;
    }

    $primaryId = $opCalendarOAuth->getPrimaryId($this->getMember());
    $service = $opCalendarOAuth->getCalendar($this->getMember());

    $event = new Google_Service_Calendar_Event();
    $event->setSummary($this->getTitle());
    $event->setDescription($this->getBody());

    $startDateTime = $this->generateDateTime();
    $start = new Google_Service_Calendar_EventDateTime();
    $start->setDateTime($startDateTime->format('c'));

    $endDateTime = $this->generateDateTime('end');
    $end = new Google_Service_Calendar_EventDateTime();
    $end->setDateTime($endDateTime->format('c'));

    $event->setStart($start);
    $event->setEnd($end);

    $source = new Google_Service_Calendar_EventSource();
    $source->setTitle($this->getTitle());

    if (!$this->getApiIdUnique())
    {
      $event = $service->events->insert($primaryId, $event);

      $this->setApiIdUnique($event->id);
    }
    else
    {
      $event = $service->events->update($primaryId, $this->getApiIdUnique(), $event);
    }

    $this->setApiEtag($event->etag);
  }

  public function generateDateTime($type = 'start')
  {
    $type = strtolower($type);

    if (!in_array($type, array('start', 'end'), true))
    {
      throw new RuntimeException;
    }

    $dateMember = $type.'_date';
    $timeMember = $type.'_time';

    return new DateTime($this->$dateMember.($this->$timeMember ? ' '.$this->$timeMember : ''));
  }

  public function isSyncable(opCalendarOAuth $opCalendarOAuth)
  {
    if (!$this->getApiIdUnique() && !$this->getMember()->getConfig(MemberConfigScheduleForm::IS_GOOGLE_CALENDAR_ALWAYS_SYNC))
    {
      return false;
    }

    return $opCalendarOAuth->authenticate($this->getMember());
  }
}
