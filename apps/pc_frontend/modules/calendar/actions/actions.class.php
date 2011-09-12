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
      elseif ($object instanceof Community)
      {
        $this->community = $object;
        $this->calendar_show_flag = $this->community->getConfig('calendar_show_flag');
        sfConfig::set('sf_nav_type', 'community');
        sfConfig::set('sf_nav_id', $this->community->id);
      }
    }

    if (!isset($this->member))
    {
      $this->member = $this->getUser()->getMember();
    }

    if (!($this->is_community = isset($this->community)))
    {
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
  }
 /**
  * Executes index action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->generateCalendar($request);
  }

 /**
  * Executes community action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeCommunity(sfWebRequest $request)
  {
    $this->forward404If('none' === $this->calendar_show_flag);
    $this->generateCalendar($request);
    $this->add_schedule = false;
  }

  protected function generateCalendar(sfWebRequest $request)
  {
    $this->setTemplate('index');
    $old_error_level = error_reporting();
    error_reporting($old_error_level & ~(E_STRICT | E_DEPRECATED));

    include_once 'Calendar/Month/Weekdays.php';

    $this->year = (int)$request->getParameter('year', date('Y'));
    $this->month = (int)$request->getParameter('month', date('n'));

    $this->add_schedule = $this->year < date('Y') || $this->year > date('Y') + 1 ? false : true;

    $first_day = sprintf('%04d-%02d-01', $this->year, $this->month);
    $end_day =  sprintf('%04d-%02d-%02d', $this->year, $this->month, date('t', strtotime($first_day)));
    if ($this->is_community)
    {
      if ('all' === $this->calendar_show_flag || 'only_community_event' === $this->calendar_show_flag)
      {
        $event_list = opCalendarPluginExtension::getMyCommunityEventByStartDayToEndDayInCommunity($this->community, $first_day, $end_day);
      }
    }
    else
    {
      $birth_list = $this->isSelf ? opCalendarPluginExtension::getScheduleBirthMemberByMonths(array($this->month)) : array();
      $event_list = $this->isSelf ? opCalendarPluginExtension::getMyCommunityEventByStartDayToEndDay($first_day, $end_day) : array();
    }

    $Month = new Calendar_Month_Weekdays($this->year, $this->month, 1);
    $Month->build();

    $this->calendar = array();
    $row = 0;
    $col = 0;
    $this->dayofweek = array(
      'class' => array('mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'),
      'item' => array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'),
    );
    while ($Day = $Month->fetch())
    {
      if ($Day->isFirst())
      {
        $row++;
        $col = 0;
      }

      $item = array(
        'dayofweek_class_name' => $this->dayofweek['class'][$col],
        'dayofweek_item_name' => $this->dayofweek['item'][$col],
      );

      $scheduleTable = Doctrine::getTable('Schedule');
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
        $schedules = array();
        if ($this->is_community)
        {
          if ('all' === $this->calendar_show_flag || 'only_member_schedule' === $this->calendar_show_flag)
          {
            $schedules = $scheduleTable->getScheduleByThisDayAndMemberInCommunity($this->community, $this->year, $this->month, $day);
          }
        }
        else
        {
          $schedules = $scheduleTable->getScheduleByThisDayAndMember($this->year, $this->month, $day, $this->member);
        }
        $item += array(
          'day' => $day,
          'today' => $is_today,
          'births' => isset($birth_list[$month_day]) ? $birth_list[$month_day] : array(),
          'events' => isset($event_list[$year_month_day]) ? $event_list[$year_month_day] : array(),
          'schedules' => $schedules,
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
    error_reporting($old_error_level);
  }
}
