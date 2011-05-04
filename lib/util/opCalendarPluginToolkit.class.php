<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opCalendarPluginToolkit
 *
 * @package    OpenPNE
 * @subpackage calendar
 * @author     Shinichi Urabe <urabe@tejimaya.com>
 */
class opCalendarPluginToolkit
{
  static $cached_emails = array();

  static public function insertSchedules($list, $public_flag, $is_save_email, $member = null)
  {
    static $first = true;

    $count = count($list);
    $okCount = 0;

    if (null === $member)
    {
      $member = sfContext::getInstance()->getUser()->getMember();
    }

    foreach ($list as $v)
    {
      $authorEmail = $v['need_convert']['creator_email'];
      if ($is_save_email && $first)
      {
        if (!$id = self::seekEmailAndGetMemberId($authorEmail))
        {
          Doctrine_Core::getTable('MemberConfig')
            ->setValue($member->id, 'opCalendarPlugin_email', $authorEmail);
        }

        $first = false;
      }
      $v['member_id'] = $member->id;

      $falseMember = 0;
      foreach ($v['need_convert']['ScheduleMember'] as $in_member)
      {
        if ($in_member['email'] == $authorEmail)
        {
          $v['ScheduleMember'][] = $member->id;
        }
        elseif ($in_id = self::seekEmailAndGetMemberId($in_member['email']))
        {
          $v['ScheduleMember'][] = $in_id;
        }
        else
        {
          $falseMember++;
        }
      }

      if ($falseMember)
      {
        $v['api_etag'] .= sprintf('_false_%d', $falseMember);
      }

      unset($v['need_convert']);
      $v['public_flag'] = $public_flag;

      if (Doctrine_Core::getTable('Schedule')->updateApiFromArray($v))
      {
        $okCount++;
      }
    }

    return $okCount === $count;
  }

  static public function seekEmailAndGetMemberId($email)
  {
    if (isset(self::$cached_emails[$email]))
    {
      return self::$cached_emails[$email];
    }
    $patterns = array('pc_address', 'mobile_address', 'opCalendarPlugin_email');
    $memberConfigTable = Doctrine_Core::getTable('MemberConfig');
    $conn = $memberConfigTable->getConnection();
    $v = array();
    foreach ($patterns as $pattern)
    {
      $v[] = '"'.$memberConfigTable->generateNameValueHash($pattern, $email).'"';
    }

    self::$cached_emails[$email] = $conn
      ->fetchOne('SELECT member_id FROM '.$memberConfigTable->getTableName().' WHERE name_value_hash IN ('.implode(',', $v).')');

    return self::$cached_emails[$email];
  }

  static public function insertInto($table, $params = array(), $conn = null, $isTimestampable = false)
  {
    if ($isTimestampable)
    {
      $date = date('Y-m-d H:i:s');
      $params['created_at'] = $date;
      $params['updated_at'] = $date;
    }

    $sql = self::getInsertQuery($table, array_keys($params));
    if (null === $conn)
    {
      $conn = Doctrine_Manager::getInstance()->getCurrentConnection();
    }
    $conn->execute($sql, array_values($params));

    return $conn->lastInsertId($table);
  }

  public static function getInsertQuery($table, $fields = array())
  {
    return 'INSERT INTO '.$table
         . ' ('.implode(', ', $fields).')'
         . ' VALUES ('.implode(', ', array_fill(0, count($fields), '?')).')';
  }
}
