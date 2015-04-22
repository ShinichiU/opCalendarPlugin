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
   * @param  Boolean $cronFlag       cron 使用有無
   * @param  Integer $publicFlag     スケジュールの公開範囲
   * @param  mixed   Member|null     Member インスタンス (Optional)
   */
  static public function updateGoogleCalendarCronFlags($cronFlag, $publicFlag, Member $member = null)
  {
    if (null === $member)
    {
      $member = sfContext::getInstance()->getMember();
    }

    $member->setConfig('google_cron_update', (bool) $cronFlag);
    $member->setConfig('google_cron_update_public_flag', (bool) $publicFlag);
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
 mc2.value AS public_flag
 FROM member_config AS mc
 INNER JOIN member_config AS mc2
 ON mc.member_id = mc2.member_id
 AND mc.name_value_hash = ?
 AND mc.name = ?
 AND mc2.name = ?
;
EOT;

    return $conn->fetchAll($sql, array(
      md5('google_cron_update,1'),
      'google_cron_update',
      'google_cron_update_public_flag',
    ));
  }

  /**
   * insertSchedules()
   *
   * APIから取得した配列をScheduleに挿入するメソッド
   *
   * @param  Array   $list         スケジュールの配列
   * @param  Integer $public_flag  スケジュールの公開範囲
   * @param  mixed   $member       Member インスタンス (Optional)
   * @return Boolean               該当する member_id、ヒットしない場合は false
   */
  static public function insertSchedules(Google_Service_Calendar_Events $events, $publicFlag, Member $member = null)
  {
    $count = count($events['items']);
    $okCount = 0;

    if (null === $member)
    {
      $member = sfContext::getInstance()->getUser()->getMember();
    }

    foreach ($events['items'] as $event)
    {
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

  public static function deleteMemberGoogleCalendar(Member $member)
  {
    Doctrine_Core::getTable('MemberConfig')->createQuery()
      ->delete()
      ->whereIn('name', array(
        'google_calendar_oauth_access_token',
        'google_cron_update',
        'google_cron_update_public_flag',
        'opCalendarPlugin_email',
      ))
      ->andWhere('member_id = ?', $member->id)
      ->execute();

    ScheduleTable::getInstance()->createQuery()
      ->delete()
      ->where('member_id = ?', $member->id)
      ->andWhere('api_id_unique IS NOT NULL')
      ->execute();
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
