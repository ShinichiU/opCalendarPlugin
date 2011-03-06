<?php

include dirname(__FILE__).'/../../bootstrap/unit.php';
$app = 'pc_frontend';
include dirname(__FILE__).'/../../bootstrap/functional.php';
include dirname(__FILE__).'/../../bootstrap/database.php';

$t = new lime_test(null, new lime_output_color());

$user = sfContext::getInstance()->getUser();
$user->setAuthenticated(true);
$user->setMemberId(2);

$t->diag('フォームのテスト');

$start = array('year' => date('Y'), 'month' => (int)date('m'), 'day' => (int)date('d'));
$start_t = array('hour' => '', 'minute' => '');
$end = array('year' => date('Y'), 'month' => (int)date('m'), 'day' => (int)date('d'));
$end_t = array('hour' => '', 'minute' => '');
$resources = array(1 => '', 2 => '', 3 => '', 4 => '', 5 => '');
$t->isa_ok(processForm($start, $start_t, $end, $end_t, $resources, array($user->getMemberId())), 'Schedule', '今日〜今日のスケジュール作成:Schedule インスタンスを返す');

$t->cmp_ok(processForm($start, $start_t, $end, $end_t, $resources, array()), '===', false, '今日〜今日のスケジュール作成、スケジュール参加者を空:isValid() false を返す');

$start = array('year' => date('Y'), 'month' => (int)date('m'), 'day' => (int)date('d'));
$start_t = array('hour' => '23', 'minute' => '15');
$end = array('year' => date('Y'), 'month' => (int)date('m'), 'day' => (int)date('d'));
$end_t = array('hour' => '23', 'minute' => '30');
$t->isa_ok(processForm($start, $start_t, $end, $end_t, $resources, array($user->getMemberId())), 'Schedule', '今日23:15 〜 23:30のスケジュール作成:Schedule インスタンスを返す');

$start = array('year' => date('Y', strtotime('yesterday')), 'month' => (int)date('m', strtotime('yesterday')), 'day' => (int)date('d', strtotime('yesterday')));
$start_t = array('hour' => '3', 'minute' => '15');
$end = array('year' => date('Y'), 'month' => (int)date('m'), 'day' => (int)date('d'));
$end_t = array('hour' => '23', 'minute' => '30');
$t->isa_ok(processForm($start, $start_t, $end, $end_t, $resources, array($user->getMemberId())), 'Schedule', '昨日の3:15 〜 今日23:30のスケジュール作成:Schedule インスタンスを返す');

$start = array('year' => date('Y', strtotime('tomorrow')), 'month' => (int)date('m', strtotime('tomorrow')), 'day' => (int)date('d', strtotime('tomorrow')));
$start_t = array('hour' => '', 'minute' => '');
$end = array('year' => date('Y'), 'month' => (int)date('m'), 'day' => (int)date('d'));
$end_t = array('hour' => '', 'minute' => '');
$t->cmp_ok(processForm($start, $start_t, $end, $end_t, $resources, array($user->getMemberId())), '===', false, '明日始り、今日終了のスケジュール作成:isValid() false を返す');

$start = array('year' => date('Y', strtotime('tomorrow')), 'month' => (int)date('m', strtotime('tomorrow')), 'day' => (int)date('d', strtotime('tomorrow')));
$start_t = array('hour' => '3', 'minute' => '15');
$end = array('year' => date('Y', strtotime('tomorrow')), 'month' => (int)date('m', strtotime('tomorrow')), 'day' => (int)date('d', strtotime('tomorrow')));
$end_t = array('hour' => '2', 'minute' => '45');
$t->cmp_ok(processForm($start, $start_t, $end, $end_t, $resources, array($user->getMemberId())), '===', false, '明日の3:15 〜 明日2:45のスケジュール作成:isValid() false を返す');

$start = array('year' => date('Y'), 'month' => (int)date('m'), 'day' => (int)date('d'));
$start_t = array('hour' => '', 'minute' => '');
$end = array('year' => date('Y'), 'month' => (int)date('m'), 'day' => (int)date('d'));
$end_t = array('hour' => '', 'minute' => '');
$resources = array(1 => '1', 2 => '', 3 => '', 4 => '', 5 => '');
$t->isa_ok(processForm($start, $start_t, $end, $end_t, $resources, array($user->getMemberId())), 'Schedule', '今日〜今日のスケジュール作成, 大会議室Aを予約:Schedule インスタンスを返す');

$t->cmp_ok(processForm($start, $start_t, $end, $end_t, $resources, array($user->getMemberId())), '===', false, '今日〜今日のスケジュール作成, 大会議室Aを連続で追加予約:isValid() false を返す');

$t->cmp_ok(processForm($start, $start_t, $end, $end_t, $resources, array($user->getMemberId())), '===', false, '今日〜今日のスケジュール作成, 大会議室Aをmember_id2で追加予約:isValid() false を返す');

$user->clearSessionData();
$user->setAuthenticated(true);
$user->setMemberId(3);
$resources = array(1 => '2', 2 => '2', 3 => '', 4 => '', 5 => '');
$t->cmp_ok(processForm($start, $start_t, $end, $end_t, $resources, array($user->getMemberId())), '===', false, '今日〜今日のスケジュール作成, 大会議室Bを二つ予約:isValid() false を返す');

$start_t = array('hour' => '1', 'minute' => '15');
$end_t = array('hour' => '1', 'minute' => '30');
$resources = array(1 => '2', 2 => '', 3 => '', 4 => '', 5 => '');
$t->isa_ok(processForm($start, $start_t, $end, $end_t, $resources, array($user->getMemberId())), 'Schedule', '今日1:15〜今日1:30のスケジュール作成, 大会議室Bを1つ予約:Schedule インスタンスを返す');

$start_t = array('hour' => '1', 'minute' => '30');
$end_t = array('hour' => '1', 'minute' => '45');
$resources = array(1 => '2', 2 => '', 3 => '', 4 => '', 5 => '');
$t->isa_ok(processForm($start, $start_t, $end, $end_t, $resources, array($user->getMemberId())), 'Schedule', '今日1:30〜今日1:45の追加スケジュール作成, 大会議室Bを1つ予約:Schedule インスタンスを返す');

$user->clearSessionData();
$user->setAuthenticated(true);
$user->setMemberId(2);
$start_t = array('hour' => '', 'minute' => '');
$end_t = array('hour' => '', 'minute' => '');
$resources = array(1 => '', 2 => '', 3 => '', 4 => '', 5 => '2');
$t->cmp_ok(processForm($start, $start_t, $end, $end_t, $resources, array($user->getMemberId())), '===', false, '今日〜今日時間指定なし　の追加スケジュール作成, 大会議室Bを1つ予約:isValid() false を返す');

$start_t = array('hour' => '1', 'minute' => '45');
$resources = array(1 => '', 2 => '', 3 => '', 4 => '', 5 => '2');
$t->isa_ok(processForm($start, $start_t, $end, $end_t, $resources, array($user->getMemberId())), 'Schedule', '今日1:45〜今日終了時間指定なし　の追加スケジュール作成, 大会議室Bを1つ予約:Schedule インスタンスを返す');

$user->clearSessionData();
$user->setAuthenticated(true);
$user->setMemberId(15);
$start = array('year' => date('Y', strtotime('+1 week 2 days')), 'month' => (int)date('m', strtotime('+1 week 2 days')), 'day' => (int)date('d', strtotime('+1 week 2 days')));
$start_t = array('hour' => '', 'minute' => '');
$end = array('year' => date('Y', strtotime('+3 week')), 'month' => (int)date('m', strtotime('+3 week')), 'day' => (int)date('d', strtotime('+3 week')));
$end_t = array('hour' => '', 'minute' => '');
$resources = array(1 => '3', 2 => '3', 3 => '3', 4 => '', 5 => '');
$t->isa_ok(processForm($start, $start_t, $end, $end_t, $resources, array($user->getMemberId())), 'Schedule', 'member_id 15 が1週間+2日〜3週間後までの追加スケジュール作成, 小会議室Aを3つ予約:Schedule インスタンスを返す');

$user->clearSessionData();
$user->setAuthenticated(true);
$user->setMemberId(13);
$resources = array(1 => '', 2 => '3', 3 => '3', 4 => '', 5 => '');
$t->isa_ok($schedule = processForm($start, $start_t, $end, $end_t, $resources, array($user->getMemberId())), 'Schedule', 'member_id 13 が1週間+2日〜3週間後までの追加スケジュール作成, 小会議室Aを2つ予約:Schedule インスタンスを返す');

$user->clearSessionData();
$user->setAuthenticated(true);
$user->setMemberId(3);
$start = array('year' => date('Y'), 'month' => (int)date('m'), 'day' => (int)date('d'));
$start_t = array('hour' => '', 'minute' => '');
$end = array('year' => date('Y'), 'month' => (int)date('m'), 'day' => (int)date('d'));
$end_t = array('hour' => '', 'minute' => '');
$resources = array(1 => '3', 2 => '', 3 => '', 4 => '', 5 => '');
$t->cmp_ok(processForm($start, $start_t, $end, $end_t, $resources, array($user->getMemberId()), 2), '===', false, 'member_id 13 が今日〜今日までの非公開の追加スケジュール作成, 小会議室Aを1つ予約:isValid() false を返す');

$user->clearSessionData();
$user->setAuthenticated(true);
$user->setMemberId(7);
$start = array('year' => date('Y', strtotime('+2 week')), 'month' => (int)date('m', strtotime('+2 week')), 'day' => (int)date('d', strtotime('+2 week')));
$start_t = array('hour' => '12', 'minute' => '0');
$end = array('year' => date('Y', strtotime('+2 week')), 'month' => (int)date('m', strtotime('+2 week')), 'day' => (int)date('d', strtotime('+2 week')));
$end_t = array('hour' => '12', 'minute' => '15');
$resources = array(1 => '3', 2 => '', 3 => '', 4 => '', 5 => '');
$t->cmp_ok(processForm($start, $start_t, $end, $end_t, $resources, array($user->getMemberId())), '===', false, 'member_id 7 が二週間後の12:00〜12:15のスケジュール作成, 小会議室Aを1つ予約:isValid() false を返す');

$schedule->delete();

$resources = array(1 => '', 2 => '3', 3 => '3', 4 => '', 5 => '');
$t->isa_ok(processForm($start, $start_t, $end, $end_t, $resources, array($user->getMemberId())), 'Schedule', 'member_id 13 がスケジュールを削除したため、member_id 2 が再度二週間後の12:00〜12:15のスケジュール作成, 小会議室Aを2つ予約:Schedule インスタンスを返す');

$user->clearSessionData();
$user->setAuthenticated(true);
$user->setMemberId(4);
$t->cmp_ok(processForm($start, $start_t, $end, $end_t, $resources, array($user->getMemberId())), '===', false, 'member_id 4が二週間後の12:00〜12:15のスケジュール作成, 小会議室Aを1つ予約:isValid() false を返す');

Doctrine::getTable('Member')->find(7)->delete();
$t->isa_ok(processForm($start, $start_t, $end, $end_t, $resources, array($user->getMemberId())), 'Schedule', 'member_id 7 が退会したので member_id 4が二週間後の12:00〜12:15のスケジュール作成, 小会議室Aを1つ予約:Schedule インスタンスを返す');

/*
 * テスト用processForm
 */
function processForm($start, $start_t, $end, $end_t, $schedule_resorces, $schedule_members = array(1), $public_flag = 1, $isUseCSRF = true)
{
  $form = new ScheduleForm();
  $form->getObject()->setMemberId(sfContext::getInstance()->getUser()->getMemberId());
  $params = array(
    'title' => 'test',
    'body'  => 'test',
    'start_date' => $start,
    'start_time' => $start_t,
    'end_date' => $end,
    'end_time' => $end_t,
    'public_flag' => $public_flag,
    'schedule_member' => $schedule_members,
    '_csrf_token' => $isUseCSRF ? $form->getCSRFToken() : '',
  );
  for ($i = 1; $i <= 5; $i++)
  {
    $params['schedule_resource_lock_'.$i] = array('schedule_resource_id' => $schedule_resorces[$i]);
  }
  $form->bind($params);

  if ($form->isValid())
  {
    return $form->save();
  }

  return false;
}
