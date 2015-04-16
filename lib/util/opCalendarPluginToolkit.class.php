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
  /**
   * updateGoogleCalendarCronFlags()
   *
   * Cronでの自動更新設定を反映するメソッド
   *
   * @param  String  $src            Google Api を更新する カレンダーのキーとなるソース
   * @param  Integer $cron_flag      cron 使用有無
   * @param  Integer $public_flag    スケジュールの公開範囲
   * @param  mixed   $member         Member インスタンス (Optional)
   */
  static public function updateGoogleCalendarCronFlags($src, $cron_flag, $public_flag, Member $member = null)
  {
    if (null === $member)
    {
      $member = sfContext::getInstance()->getMember();
    }

    $member->setConfig('google_cron_update', $cron_flag);
    if (!$cron_flag)
    {
      return false;
    }

    $crondata = $member->getConfig('google_cron_update_params');
    $update_params = array();
    if ($crondata)
    {
      $update_params = unserialize($crondata);
    }
    $update_params['src'][] = $src;
    $update_params['src'] = array_unique($update_params['src']);
    $update_params['public_flag'] = $public_flag;
    $member->setConfig('google_cron_update_params', serialize($update_params));
  }

  /**
   * getAllGoogleCalendarCronConfig()
   *
   * Cronでの自動更新設定を取得するメソッド
   *
   */
  static public function getAllGoogleCalendarCronConfig()
  {
    $scheduleMemberTable = Doctrine_Core::getTable('MemberConfig');
    $conn = $scheduleMemberTable->getConnection();

    $mctable = $scheduleMemberTable->getTableName();
    $sql = 'SELECT mc.member_id as member_id, mc2.value as serial,'
         . ' mc3.value as token, mc4.value as secret'
         . ' FROM '.$mctable.' as mc'
         . ' JOIN '.$mctable.' as mc2 ON mc.member_id = mc2.member_id'
         . ' JOIN '.$mctable.' as mc3 ON mc.member_id = mc3.member_id'
         . ' JOIN '.$mctable.' as mc4 ON mc.member_id = mc4.member_id'
         . ' WHERE mc.name = ? AND mc.value = ?'
         . ' AND mc2.name = ? AND mc3.name = ? AND mc4.name = ?';

    return $conn->fetchAll($sql, array(
      'google_cron_update',
      1,
      'google_cron_update_params',
      'google_calendar_oauth_token',
      'google_calendar_oauth_token_secret',
    ));
  }

  /**
   * insertSchedules()
   *
   * APIから取得した配列をScheduleに挿入するメソッド
   *
   * @param  Array   $list           スケジュールの配列
   * @param  Integer $public_flag    スケジュールの公開範囲
   * @param  Boolean $isSaveEmail  APIから取得した本人のメールアドレスを保存するか否か
   * @param  mixed   $member         Member インスタンス (Optional)
   * @return Boolean 該当する member_id、ヒットしない場合は false
   */
  static public function insertSchedules(Google_Service_Calendar_Events $events, $publicFlag, $isSaveEmail = true, Member $member = null)
  {
    static $first = true;

    $count = count($events['items']);
    $okCount = 0;

    if (null === $member)
    {
      $member = sfContext::getInstance()->getUser()->getMember();
    }

    foreach ($events['items'] as $event)
    {
      $authorEmail = $event->email;
      if ($isSaveEmail && $first)
      {
        if (!$id = self::seekEmailAndGetMemberId($authorEmail))
        {
          $member->setConfig('opCalendarPlugin_email', $authorEmail);
        }

        $first = false;
      }

      if (Doctrine_Core::getTable('Schedule')->updateApiFromEvent($event, $member, $publicFlag))
      {
        $okCount++;
      }
    }

    return $okCount === $count;
  }

  /**
   * $cached_emails
   *
   * seekEmailAndGetMemberId() メソッドで同じメールアドレスの検索クエリを呼び出す回数を減らすためのものです
   */
  static $cached_emails = array();

  /**
   * seekEmailAndGetMemberId()
   *
   * member_config にメールアドレスが登録されているかを検索するメソッド
   *
   * @param  String $email 検索するメールアドレス
   * @return mixed 該当する member_id、ヒットしない場合は false
   */
  static public function seekEmailAndGetMemberId($email)
  {
    if (isset(self::$cached_emails[$email]))
    {
      return self::$cached_emails[$email];
    }
    $patterns = array('pc_address', 'mobile_address', 'opCalendarPlugin_email');
    $memberConfigTable = Doctrine_Core::getTable('MemberConfig');
    $conn = $memberConfigTable->getConnection();

    $sql = 'SELECT member_id FROM '.$memberConfigTable->getTableName();
    $params = array();
    $first = true;
    foreach ($patterns as $pattern)
    {
      $sql .= $first ? ' WHERE name_value_hash = ?' : ' OR name_value_hash = ?';
      $params[] = $memberConfigTable->generateNameValueHash($pattern, $email);
      $first = false;
    }

    self::$cached_emails[$email] = $conn->fetchOne($sql, $params);

    return self::$cached_emails[$email];
  }

  /**
   * insertInto()
   *
   * SQL を使って配列からインサート文を生成し、データを挿入するメソッド
   *
   * ※  第二引数の配列には SQL Injection 防止のため
   * key には動的な値を挿入できないようにしてください
   *
   * @param  String  $table データ挿入先テーブル名
   * @param  Array   $params key をカラム名、value に値の組み合わせの1レコード分のデータ
   * @param  Object  $conn コネクションオブジェクト
   * @param  Boolean $isTimestampable timestampable アクティブビヘイビアのデータを挿入します
   * @return Integer 挿入したレコードのプライマリーキーが返ってきます
   */
  static public function insertInto($table, Array $params = array(), $conn = null, $isTimestampable = false)
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

  /**
   * update()
   *
   * SQL を使って配列からアップデート文を生成し、データを更新するメソッド
   *
   * ※  第二引数、第三引数の配列には SQL Injection 防止のため
   * key には動的な値を挿入できないようにしてください
   *
   * @param  String $table データ挿入先テーブル名
   * @param  Array  $params key をカラム名、value に値の組み合わせの1レコード分のデータ
   * @param  Array  $wheres key をカラム名、value に検索対象の値
   * @param  Object  $conn コネクションオブジェクト
   * @param  Boolean $isTimestampable timestampable アクティブビヘイビアのデータを挿入します
   * @return Bool 更新に成功したかどうか
   */
  static public function update($table, Array $params = array(), $wheres = array(), $conn = null, $isTimestampable = false)
  {
    if ($isTimestampable)
    {
      $date = date('Y-m-d H:i:s');
      $params['updated_at'] = $date;
    }

    $sql = self::getUpdateQuery($table, array_keys($params), array_keys($wheres));
    if (null === $conn)
    {
      $conn = Doctrine_Manager::getInstance()->getCurrentConnection();
    }

    return $conn->execute($sql, array_merge(array_values($params), array_values($wheres)));
  }

  public static function getUpdateQuery($table, $fields = array(), $whereFields = array())
  {
    $sets = array();
    foreach ($fields as $field)
    {
      $sets[] = sprintf('%s = ?', $field);
    }
    $wheres = array();
    foreach ($whereFields as $whereField)
    {
      $wheres[] = sprintf('%s = ?', $whereField);
    }
    return 'UPDATE '.$table
         . ' SET '.implode(', ', $sets)
         . ' WHERE '.implode(' AND ', $wheres);
  }

  public static function getLastDay($month)
  {
    $limitedMonths = array(
      2 => self::isLeap((int)date('Y')) ? 28 : 29,
      4 => 30,
      6 => 30,
      9 => 30,
      11 => 30,
    );
    if (isset($limitedMonths[$month]))
    {
      return $limitedMonths[$month];
    }

    return 31;
  }

  public static function isLeap($year)
  {
    if (0 == $year % 4)
    {
      if (0 == $year % 100)
      {
        if (0 == $year % 400)
        {
          return true;
        }

        return false;
      }

      return true;
    }

    return false;
  }
}
