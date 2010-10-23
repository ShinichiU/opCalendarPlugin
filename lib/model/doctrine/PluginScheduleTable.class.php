<?php
/**
 */
class PluginScheduleTable extends Doctrine_Table
{
  const PUBLIC_FLAG_SNS              = 1;
  const PUBLIC_FLAG_SCHEDULE_MEMBER  = 2;

  protected static $publicFlags = array(
    self::PUBLIC_FLAG_SNS              => 'All Members',
    self::PUBLIC_FLAG_SCHEDULE_MEMBER  => 'Only schedule member',
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

  public function getScheduleByThisDay($year, $month, $day)
  {
    $day = sprintf('%04d-%02d-%02d', (int)$year, (int)$month, (int)$day);
    $scheduleIds = Doctrine::getTable('ScheduleMember')->getScheduleIdsByMemberId($this->getMyId());

    $q = $this->createQuery()
      ->select('id, title')
      ->where('start_date <= ?', $day)
      ->andWhere('end_date >= ?', $day);
    if (!count($scheduleIds))
    {
      $q->andWhere('member_id = ?', (int)$this->getMyId());
    }
    else
    {
      $q->andWhere('member_id = ? OR id IN ('.implode(', ', $scheduleIds).')', (int)$this->getMyId());
    }
    $values = $q->execute(array(), Doctrine::HYDRATE_NONE);

    if (!count($values))
    {
      return array();
    }
    $results = array();
    foreach ($values as $value)
    {
      $results[] = array(
        'id' => $value[0],
        'title' => $value[1],
      );
    }

    return $results;
  }

  private function getMyId()
  {
    static $memberId;

    if (!isset($memberId))
    {
      $memberId = sfContext::getInstance()->getUser()->getMemberId();
    }

    return $memberId;
  }
}
