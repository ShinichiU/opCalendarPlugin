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
   * insertSchedules()
   *
   * APIから取得した配列をScheduleに挿入するメソッド
   *
   * @param  Array   $list           スケジュールの配列
   * @param  Integer $public_flag    スケジュールの公開範囲
   * @param  Boolean $is_save_email  APIから取得した本人のメールアドレスを保存するか否か
   * @param  mixed   $member         Member インスタンス (Optional)
   * @return Boolean 該当する member_id、ヒットしない場合は false
   */
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
   * seekEmailAndGetMemberId()
   *
   * SQL を使って配列からインサート文を生成し、データを挿入するメソッド
   *
   * @param  String $table データ挿入先テーブル名
   * @param  Array  $params key をカラム名、value に値の組み合わせの1レコード分のデータ ※ (SQL Injection 防止のために key には動的な値を挿入できないようにしてください)
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
}
