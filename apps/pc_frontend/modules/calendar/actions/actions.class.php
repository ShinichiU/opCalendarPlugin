<?php

/**
 * calendar actions.
 *
 * @package    OpenPNE
 * @subpackage calendar
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 9301 2008-05-27 01:08:46Z dwhittle $
 */
class calendarActions extends sfActions
{
  public function preExecute()
  {
    if (is_callable(array($this->getRoute(), 'getObject')))
    {
      $object = $this->getRoute()->getObject();
      if ($object instanceof Member)
      {
        $this->member = $object;
      }
    }

    if (!isset($this->member))
    {
      $this->member = $this->getUser()->getMember();
    }

    $this->isSelf = true;
    if ($this->member->id !== $this->getUser()->getMemberId())
    {
      $this->isSelf = false;
      sfConfig::set('sf_nav_type', 'friend');
      sfConfig::set('sf_nav_id', $this->member->id);
      $relation = Doctrine::getTable('MemberRelationship')->retrieveByFromAndTo($this->member->id, $this->getUser()->getMemberId());
      $this->forwardIf($relation && $relation->is_access_block, 'default', 'error');
    }
  }
 /**
  * Executes index action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    include_once 'Calendar/Month/Weekdays.php';
    $this->year = (int)$request->getParameter('year', date('Y'));
    $this->month = (int)$request->getParameter('month', date('n'));

    $this->add_schedule = $this->year < date('Y') || $this->year > date('Y') + 1 ? false : true;

    $birth_list = $this->isSelf ? opCalendarPluginExtension::getScheduleBirthMemberByMonths(array($this->month)) : array();
    $first_day = sprintf('%04d-%02d-01', $this->year, $this->month);
    $end_day =  sprintf('%04d-%02d-%02d', $this->year, $this->month, date('t', strtotime($first_day)));
    $event_list = $this->isSelf ? opCalendarPluginExtension::getMyCommunityEventByStartDayToEndDay($first_day, $end_day) : array();

    $Month = new Calendar_Month_Weekdays($this->year, $this->month, 1);
    $Month->build();

    $this->calendar = array();
    $row = 0;
    $col = 0;
    while ($Day = $Month->fetch())
    {
      if ($Day->isFirst())
      {
        $row++;
        $col = 0;
      }

      $item = array();

      if ($Day->isEmpty())
      {
        $this->calendar[$row][$col++] = $item;
      }
      else
      {
        $day = $Day->thisDay();
        $month_day = sprintf('%02d-%02d', $this->month, $day);
        $year_month_day = sprintf('%04d-%s', $this->year, $month_day);
        $is_today = (int)date('Y') === $this->year && (int)date('n') === $this->month && (int)date('d') === $day;
        $item += array(
          'day' => $day,
          'today' => $is_today,
          'births' => isset($birth_list[$month_day]) ? $birth_list[$month_day] : array(),
          'events' => isset($event_list[$year_month_day]) ? $event_list[$year_month_day] : array(),
          'schedules' => Doctrine::getTable('Schedule')->getScheduleByThisDayAndMember($this->year, $this->month, $day, $this->member),
          'holidays' => Doctrine::getTable('Holiday')->getByYearAndMonthAndDay($this->year, $this->month, $day),
        );

        $this->calendar[$row][$col++] = $item;
      }
    }

    $this->ym = array(
      'year_disp'  => $this->year,
      'month_disp' => $this->month,
      'year_prev'  => date('Y', $Month->prevMonth(true)),
      'month_prev' => date('n', $Month->prevMonth(true)),
      'year_next'  => date('Y', $Month->nextMonth(true)),
      'month_next' => date('n', $Month->nextMonth(true)),
    );

  }
}
