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

  public function updateApiFromArray($list)
  {
    $scheduleMemberTable = Doctrine_Core::getTable('ScheduleMember');
    $conn = $this->getConnection();
    $conn->beginTransaction();

    try
    {
      $sql = 'SELECT id, api_etag FROM '.$this->getTableName()
           . ' WHERE api_flag = ? AND api_id_unique = ?';
      $params = array($list['api_flag'], $list['api_id_unique']);
      if ($schedule = $conn->fetchRow($sql, $params))
      {
        $id = $schedule['id'];
        if ($list['api_etag'] === $schedule['api_etag'])
        {
          $conn->rollback();
          return $id;
        }

        $sql = 'DELETE '.$scheduleMemberTable->getTableName()
             . ' FROM '.$scheduleMemberTable->getTableName()
             . ' WHERE schedule_id = ?';
        $conn->execute($sql, array((int)$id));
      }

      $scheduleMembers = array_unique($list['ScheduleMember']);
      unset($list['ScheduleMember']);

      if (!isset($id))
      {
        if (!$id = opCalendarPluginToolkit::insertInto($this->getTableName(), $list, $conn))
        {
          throw new Exception('schedule commit error.');
        }
      }
      else
      {
        if (!opCalendarPluginToolkit::update($this->getTableName(), $list, array('id' => (int)$id), $conn))
        {
          throw new Exception('schedule commit error.');
        }
      }

      $members = array();
      foreach ($scheduleMembers as $member_id)
      {
        $members['member_id'] = $member_id;
        $members['schedule_id'] = $id;
        opCalendarPluginToolkit::insertInto($scheduleMemberTable->getTableName(), $members, $conn);
      }

      $conn->commit();
    }
    catch (Exception $e)
    {
      $conn->rollback();

      throw $e;
    }

    return $id;
  }
}
