<?php
/**
 */
class PluginHolidayTable extends Doctrine_Table
{
  public function getByYearAndMonthAndDay($year, $month, $day)
  {
    if (!$year || !$month || !$day)
    {
      return array();
    }
    $results = $this->createQuery()
      ->select('name')
      ->where('year = ? OR year IS NULL', (int)$year)
      ->andWhere('month = ?', (int)$month)
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
      ->orderBy('year ASC')
      ->addOrderBy('month ASC')
      ->addOrderBy('day ASC')
      ->execute();

    return $results;
  }
}
