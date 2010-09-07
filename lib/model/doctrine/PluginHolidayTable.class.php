<?php
/**
 */
class PluginHolidayTable extends Doctrine_Table
{
  public function getByMonthAndDay($month, $day)
  {
    if (!$month || !$day)
    {
      return array();
    }
    $results = $this->createQuery()
      ->select('name')
      ->where('month = ?', (int)$month)
      ->andWhere('day = ?', (int)$day)
      ->execute(array(), Doctrine::HYDRATE_NONE);

    if (!count($results))
    {
      return array();
    }

    $holidays = array();
    foreach ($results as $v)
    {
      $holidays[] = $v[0];
    }

    return $holidays;
  }

  public function getHolidayList()
  {
    $results = $this->createQuery()
      ->orderBy('month ASC')
      ->addOrderBy('day ASC')
      ->execute();

    return $results;
  }
}
