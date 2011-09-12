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
    if ($request->hasParameter('id') && $request->getParameter('module') == 'member' && $request->getParameter('action') == 'profile')
    {
      $this->member = Doctrine::getTable('Member')->find($request->getParameter('id'));
    }
    else
    {
      $this->member = $this->getUser()->getMember();
    }

    $this->isSelf = $this->member->id === $this->getUser()->getMemberId();
    $this->w = (int)$request->getParameter('calendar_weekparam', 0);
    $this->pw = $this->w - 1;
    $this->nw = $this->w + 1;
    $this->calendar = $this->getCalendar($this->w);
    $this->form = new MiniScheduleForm(array(), array('calendar' => $this->calendar));
  }

  public function executeCommunityCalendar(sfWebRequest $request)
  {
    if (!$request->hasParameter('id'))
    {
      return sfView::NONE;
    }
    $this->community = Doctrine_Core::getTable('Community')->find($id = (int)$request->getParameter('id'));

    // default none === calendar_show_flag
    if (!($this->calendar_show_flag = $this->community->getConfig('calendar_show_flag')) || 'none' === $this->calendar_show_flag)
    {
      return sfView::NONE;
    }

    $this->w = (int)$request->getParameter('calendar_weekparam', 0);
    $this->pw = $this->w - 1;
    $this->nw = $this->w + 1;
    $this->calendar = $this->getCommunityCalendar($this->community, $this->w);
    //$this->form = new MiniScheduleForm(array(), array('calendar' => $this->calendar));
  }

  private function getCommunityCalendar(Community $community, $w = 0)
  {
    return $this->getCalendar($w, $community);
  }

  private function getMemberCalendar($w = 0)
  {
    return $this->getCalendar($w);
  }

  private function getCalendar($w = 0, $community = null)
  {
    $old_error_level = error_reporting();
    error_reporting($old_error_level & ~(E_STRICT | E_DEPRECATED));

    include_once 'Calendar/Week.php';
    $time = strtotime($w . ' week');

    $Week = new Calendar_Week(date('Y', $time), date('m', $time), date('d', $time), 1);
    $Week->build();
    $dayofweek = array(
      'class' => array('mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'),
      'item' => array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'),
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
        'dayofweek_class_name' => $dayofweek['class'][$i],
        'dayofweek_item_name' => $dayofweek['item'][$i],
        'holidays' => Doctrine::getTable('Holiday')->getByYearAndMonthAndDay($y, $m, $d),
      );

      if (null === $community)
      {
        // member home or profile home calendar.
        $item['births'] = $this->isSelf ? opCalendarPluginExtension::getScheduleBirthMemberByTargetDay($m, $d) : array();
        $item['events'] = $this->isSelf ? opCalendarPluginExtension::getMyCommunityEventByTargetDay($y, $m, $d) : array();
        $item['schedules'] = Doctrine::getTable('Schedule')->getScheduleByThisDayAndMember($y, $m, $d, $this->member);
      }
      else
      {
        // community home calendar.
        $item['events'] = array();
        $item['schedules'] = array();

        if ('all' === $this->calendar_show_flag || 'only_community_event' === $this->calendar_show_flag)
        {
          // only open to all sns member schedule.
          $item['events'] = opCalendarPluginExtension::getMyCommunityEventByTargetDayInCommunity($community, $y, $m, $d);
        }
        if ('all' === $this->calendar_show_flag || 'only_member_schedule' === $this->calendar_show_flag)
        {
          // only open to all sns member schedule.
          $item['schedules'] = Doctrine::getTable('Schedule')->getScheduleByThisDayAndMemberInCommunity($community, $y, $m, $d, $this->member);
        }
      }

      $calendar[$i++] = $item;
    }
    error_reporting($old_error_level);

    return $calendar;
  }
}
