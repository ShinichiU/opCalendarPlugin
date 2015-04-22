<?php
/**
 */
class PluginScheduleTable extends Doctrine_Table
{
  const PUBLIC_FLAG_SNS              = 1;
  const PUBLIC_FLAG_SCHEDULE_MEMBER  = 2;

  // api flag
  const GOOGLE_CALENDAR              = 1;

  protected static $publicFlags = array(
    self::PUBLIC_FLAG_SNS              => 'All Members',
    self::PUBLIC_FLAG_SCHEDULE_MEMBER  => 'Participants only scheduled public',
  );

  public function getPublicFlags()
  {
    $publicFlags = array();
    foreach (self::$publicFlags as $key => $publicFlag)
    {
      $publicFlags[$key] = sfContext::getInstance()->getI18N()->__($publicFlag);
    }

    return $publicFlags;
  }

  // only open to all sns member schedule.
  public function getScheduleByThisDayAndMemberInCommunity(Community $community, $year, $month, $day)
  {
    $day = sprintf('%04d-%02d-%02d', (int)$year, (int)$month, (int)$day);
    $memberIds = array();
    foreach ($community->getMembers() as $member)
    {
      $memberIds[] = $member->id;
    }
    if (!$memberIds)
    {
      return array();
    }

    return $this->createQuery()
      ->select('id, title')
      ->where('start_date <= ?', $day)
      ->andWhere('end_date >= ?', $day)
      ->andWhere('public_flag = ?', PluginScheduleTable::PUBLIC_FLAG_SNS)
      ->andWhere('member_id IN ('.implode(', ', $memberIds).')')
      ->execute();
  }

  public function getScheduleByThisDayAndMember($year, $month, $day, Member $member)
  {
    $day = sprintf('%04d-%02d-%02d', (int)$year, (int)$month, (int)$day);
    $scheduleIds = Doctrine::getTable('ScheduleMember')->getScheduleIdsByMemberId($member->getId());

    $q = $this->createQuery()
      ->select('id, title')
      ->where('start_date <= ?', $day)
      ->andWhere('end_date >= ?', $day);
    if (!count($scheduleIds))
    {
      $q->andWhere('member_id = ?', (int)$member->getId());
    }
    else
    {
      $q->andWhere('member_id = ? OR id IN ('.implode(', ', $scheduleIds).')', (int)$member->getId());
    }

    return $q->execute();
  }

  public function updateApiFromEvent(Google_Service_Calendar_Event $event, Member $member, $publicFlag)
  {
    $conn = $this->getConnection();
    $conn->beginTransaction();

    try
    {
      $schedule = $this->findOneByApiIdUnique($event->id);

      if ('cancelled' === $event->status)
      {
        if ($schedule)
        {
          $schedule->delete();
        }

        $conn->commit();

        return true;
      }

      if (!$schedule)
      {
        $schedule = new Schedule;
        $schedule->setApiIdUnique($event->id);
      }
      elseif ($event->etag === $schedule->api_etag)
      {
        $conn->rollback();

        return $schedule->id;
      }

      $schedule->setTitle($event->summary);
      $schedule->setBody($event->description);
      $schedule->setMember($member);
      $schedule->setPublicFlag($this->getEventPublicFlag($event, $publicFlag));

      if ($event->start->dateTime)
      {
        $startDateTime = new DateTime($event->start->dateTime);
        $schedule->setStartDate($startDateTime->format('Y-m-d'));
        $schedule->setStartTime($startDateTime->format('H:i:s'));
      }
      elseif ($event->start->date)
      {
        $schedule->setStartDate($event->start->date);
      }

      if ($event->end->dateTime)
      {
        $endDateTime = new DateTime($event->end->dateTime);
        $schedule->setEndDate($endDateTime->format('Y-m-d'));
        $schedule->setEndTime($endDateTime->format('H:i:s'));
      }
      elseif ($event->end->date)
      {
        $schedule->setStartDate($event->end->date);
      }

      $schedule->setApiEtag($event->etag);
      $schedule->save();

      ScheduleMemberTable::getInstance()->updateScheduleMember(array(
        'schedule_id' => $schedule->id,
        'member_id' => $schedule->member_id,
      ));

      foreach ($event->attendees as $attendee)
      {
        $memberId = opCalendarPluginToolkit::seekEmailAndGetMemberId($attendee->email);
        if (!$memberId)
        {
          continue;
        }

        ScheduleMemberTable::getInstance()->updateScheduleMember(array(
          'schedule_id' => $schedule->id,
          'member_id' => $memberId,
        ));
      }

      $conn->commit();
    }
    catch (Exception $e)
    {
      $conn->rollback();

      throw $e;
    }

    return $schedule->id;
  }

  protected function getEventPublicFlag(Google_Service_Calendar_Event $event, $publicFlag)
  {
    return 'public' !== $event->visibility ? self::PUBLIC_FLAG_SCHEDULE_MEMBER : $publicFlag;
  }
}
