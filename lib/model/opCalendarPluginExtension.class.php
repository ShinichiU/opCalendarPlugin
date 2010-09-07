<?php
/**
 */
class opCalendarPluginExtension
{
  private static
    $myId = null,
    $birth_prof_id = null,
    $friendIds = null,
    $communityMemberIds = null,
    $joinEvents = null;

  public static function getScheduleBirthMemberByMonths(array $months)
  {
    return self::getScheduleBirthMember($months);
  }

  public static function getScheduleBirthMemberByTargetDay($month, $day)
  {
    return self::getScheduleBirthMember(array($month), $day, false);
  }

  private static function getScheduleBirthMember(array $months, $day = null, $is_setKeydate = true)
  {
    $memberId = self::getMyId();

    if (is_null(self::$birth_prof_id))
    {
      $profile = Doctrine::getTable('Profile')->createQuery()
        ->select('id')
        ->where('name = ?', 'op_preset_birthday')
        ->fetchOne(array(), Doctrine::HYDRATE_NONE);
      if (!$profile)
      {
        return array();
      }
      self::$birth_prof_id = $profile[0];
    }

    if (is_null(self::$friendIds))
    {
      self::$friendIds = Doctrine::getTable('MemberRelationship')->getFriendMemberIds($memberId);
      self::$friendIds[] = $memberId;
    }

    $q = Doctrine::getTable('MemberProfile')->createQuery()
      ->select('member_id, value_datetime, public_flag')
      ->where('profile_id = ?', self::$birth_prof_id)
      ->andWhereIn('member_id', self::$friendIds);

    foreach ($months as $month)
    {
      $targetValue = $day ? array('%m-%d', sprintf('%02d-%02d', (int)$month, (int)$day)) : array('%m', sprintf('%02d', (int)$month));
      $q->andWhere('DATE_FORMAT(value_datetime, ?) = ?', $targetValue);
    }
    $birthResults = $q->execute(array(), Doctrine::HYDRATE_NONE);

    if (!count($birthResults))
    {
      return array();
    }

    $results = array();
    foreach ($birthResults as $birthResult)
    {
      if ($memberId != $birthResult[0] && ProfileTable::PUBLIC_FLAG_PRIVATE == $birthResult[2])
      {
        continue;
      }
      $member = Doctrine::getTable('Member')->find($birthResult[0]);
      if ($is_setKeydate)
      {
        $results[substr($birthResult[1], 5, 5)][] = $member;
      }
      else
      {
        $results[] = $member;
      }
    }

    return $results;
  }

  public static function getMyCommunityEventByTargetDay($year, $month, $day)
  {
    return self::getMyCommunityEvent(null, null, sprintf('%04d-%02d-%02d', (int)$year, (int)$month, (int)$day), false);
  }

  public static function getMyCommunityEventByStartDayToEndDay($startday, $endday)
  {
    return self::getMyCommunityEvent($startday, $endday);
  }

  private static function getMyCommunityEvent($startday = null, $endday = null, $targetDay = null, $is_setKeydate = true)
  {
    $memberId = self::getMyId();

    if (is_null(self::$communityMemberIds))
    {
      $communityMembers = Doctrine::getTable('CommunityMember')->createQuery()
        ->select('community_id')
        ->where('member_id = ?', (int)$memberId)
        ->andWhere('is_pre = ?', false)
        ->execute(array(), Doctrine::HYDRATE_NONE);

      self::$communityMemberIds = array();
      foreach ($communityMembers as $communityMember)
      {
        self::$communityMemberIds[] = $communityMember[0];
      }
    }
    if (!count(self::$communityMemberIds))
    {
      return array();
    }

    $q = Doctrine::getTable('CommunityEvent')->createQuery()
      ->select('id, name, DATE(open_date)')
      ->whereIn('community_id', self::$communityMemberIds);

    if ($targetDay)
    {
      $q->andWhere('open_date = ?', $targetDay);
    }
    else
    {
      $q->andWhere('open_date >= ?', $startday)
        ->andWhere('open_date <= ?', $endday);
    }

    $communityEvents = $q->execute(array(), Doctrine::HYDRATE_NONE);
    if (!count($communityEvents))
    {
      return array();
    }

    if (is_null(self::$joinEvents))
    {
      $communityEventMembers = Doctrine::getTable('CommunityEventMember')->createQuery()
        ->select('community_event_id')
        ->where('member_id = ?', $memberId)
        ->execute(array(), Doctrine::HYDRATE_NONE);

      self::$joinEvents = array();
      foreach ($communityEventMembers as $communityEventMember)
      {
        self::$joinEvents[$communityEventMember[0]] = true;
      }
    }

    $results = array();
    foreach ($communityEvents as $communityEvent)
    {
      $data = array(
        'is_join' => isset(self::$joinEvents[$communityEvent[0]]) ? true : false,
        'id' => $communityEvent[0],
        'name' => $communityEvent[1],
      );
      if ($is_setKeydate)
      {
        $results[$communityEvent[2]][] = $data;
      }
      else
      {
        $results[] = $data;
      }
    }

    return $results;
  }

  private static function getMyId()
  {
    if (is_null(self::$myId))
    {
      self::$myId = sfContext::getInstance()->getUser()->getMemberId();
    }

    return self::$myId;
  }
}
