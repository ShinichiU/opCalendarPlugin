<?php
/**
 */
class PluginScheduleTable extends Doctrine_Table
{
  private $memberId = null;

  public function getScheduleByThisDay($year, $month, $day)
  {
    $day = sprintf('%04d-%02d-%02d', (int)$year, (int)$month, (int)$day);
    $this->getMyId();

    $values = $this->createQuery()
      ->select('id, title')
      ->where('start_date <= ?', $day)
      ->andWhere('end_date >= ?', $day)
      ->andWhere('member_id = ?', (int)$this->memberId)
      ->execute(array(), Doctrine::HYDRATE_NONE);

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
    if (is_null($this->memberId))
    {
      $this->memberId = sfContext::getInstance()->getUser()->getMemberId();
    }

    return $this->memberId;
  }
}
