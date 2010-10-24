<?php
/**
 */
class PluginScheduleTable extends Doctrine_Table
{
  const PUBLIC_FLAG_SNS              = 1;
  const PUBLIC_FLAG_SCHEDULE_MEMBER  = 2;

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
}
