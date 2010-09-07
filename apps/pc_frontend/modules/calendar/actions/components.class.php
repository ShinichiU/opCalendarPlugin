<?php

/**
 * calendar components.
 *
 * @package    OpenPNE
 * @subpackage calendar
 * @author     Shinichi Urabe <urabe@tejimaya.com>
 */
class calendarComponents extends sfComponents
{
  public function executeWeekly(sfWebRequest $request)
  {
    $this->w = (int)$request->getParameter('calendar_weekparam', 0);
    $this->pw = $this->w - 1;
    $this->nw = $this->w + 1;
    $this->calendar = $this->getCalendar($this->w);
    $this->form = new MiniScheduleForm(array(), array('calendar' => $this->calendar));
  }

  private function getCalendar($w = 0)
  {
    include_once 'Calendar/Week.php';
    $time = strtotime($w . ' week');
    $Week = new Calendar_Week(date('Y', $time), date('m', $time), date('d', $time), 1);
    $Week->build();
    $dayofweek = array(
      'en' => array('mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'),
      'ja_JP' => array('月', '火', '水', '木', '金', '土', '日'),
    );
    $calendar = array();
    $i = 0;
    while ($Day = $Week->fetch())
    {
      $y = $Day->thisYear();
      $m = $Day->thisMonth();
      $d = $Day->thisDay();
      $item = array(
        'year'=> $y,
        'month'=> $m,
        'day' => $d,
        'today' => 0 === $w && (int)date('d') === $d,
        'dayofweek_en' => $dayofweek['en'][$i],
        'dayofweek_ja' => $dayofweek['ja_JP'][$i],
        'births' => opCalendarPluginExtension::getScheduleBirthMemberByTargetDay($m, $d),
        'events' => opCalendarPluginExtension::getMyCommunityEventByTargetDay($y, $m, $d),
        'schedules' => Doctrine::getTable('Schedule')->getScheduleByThisDay($y, $m, $d),
        'holidays' => Doctrine::getTable('Holiday')->getByMonthAndDay($m, $d),
      );

      $calendar[$i++] = $item;
    }
    return $calendar;
  }
}
