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
    $conn = ScheduleMemberTable::getInstance()->getConnection();

    $sql = <<<EOT
SELECT
 mc.member_id AS member_id,
 mc2.value AS serial
 FROM member_config AS mc
 LEFT JOIN member_config AS mc2
 ON mc.member_id = mc2.member_id
 AND mc.name_value_hash = ?
 AND mc.name = ?
 AND mc2.name = ?
;
EOT;

    return $conn->fetchAll($sql, array(
      md5('google_cron_update,1'),
      'google_cron_update',
      'google_cron_update_params',
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

  public static function getLastDay($month, $year = null)
  {
    $year = $year ? $year : date('Y');
    $limitedMonths = array(
      2 => self::isLeap((int)$year) ? 28 : 29,
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
