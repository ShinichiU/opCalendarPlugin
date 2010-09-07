<div class="dparts monthlyCalendarTable"><div class="parts">
<div class="partsHeading"><h3><?php echo sprintf('%04d年%02d月', $ym['year_disp'], $ym['month_disp']) ?>のカレンダー</h3></div>

<div class="block topBox">
<p class="moreInfo"><?php echo image_tag('/opCalendarPlugin/images/icon_schedule.gif', array('alt' => '')) ?> <?php echo link_to('予定を追加する', '@schedule_new') ?></p>
<p class="pager"><?php echo link_to('&lt;&lt; 前の月', sprintf('@calendar_year_month?year=%d&month=%d', $ym['year_prev'], $ym['month_prev']), array('class' => 'prev')) ?>
 | <?php echo link_to('今月', '@calendar', array('class' => 'curr')) ?>
 | <?php echo link_to('次の月 &gt;&gt;', sprintf('@calendar_year_month?year=%d&month=%d', $ym['year_next'], $ym['month_next']), array('class' => 'next')) ?></p>
</div>

<table class="calendar">
<colgroup class="mon"></colgroup>
<colgroup class="tue"></colgroup>
<colgroup class="wed"></colgroup>
<colgroup class="thu"></colgroup>
<colgroup class="fri"></colgroup>
<colgroup class="sat"></colgroup>
<colgroup class="sun"></colgroup>
<thead>
<tr>
<th class="mon">月</th>
<th class="tue">火</th>
<th class="wed">水</th>
<th class="thu">木</th>
<th class="fri">金</th>
<th class="sat">土</th>
<th class="sun">日</th>
</tr>
</thead>
<tbody>
<?php foreach ($calendar as $week): ?>

<tr>
<?php foreach ($week as $item): ?>

<?php
if (!isset($item['day']))
{
  echo sprintf('<td class="%s empty"></td>', $item['dayofweek_en']), "\n";
  continue;
}

$cls_today = $item['today'] ? ' today' : '';
$cls_holiday = count($item['holidays']) ? ' holiday' : '';
?>
<?php echo sprintf('<td class="%s%s%s">', $item['dayofweek_en'], $cls_today, $cls_holiday), "\n" ?>
<p class="day"><span class="date"><?php echo $item['day'] ?></span></p>
<?php if ($add_schedule): ?>
<p class="new_schedule"><?php echo link_to(image_tag('/opCalendarPlugin/images/icon_schedule.gif', array('alt' => '予定を追加する')), '@schedule_new_for_this_date?year='.$ym['year_disp'].'&month='.$ym['month_disp'].'&day='.$item['day']) ?></p>
<?php endif ?>
<?php foreach ($item['holidays'] as $holiday): ?>
<p class="holiday"><?php echo $holiday ?></p>
<?php endforeach ?>
<?php foreach ($item['births'] as $member): ?>
<p class="birthday"><?php echo op_link_to_member($member, array('link_target' => sprintf('<span class="icon">%s </span>%sさん', image_tag('/opCalendarPlugin/images/icon_birthday.gif', array('alt' => '[誕]')), $member->getName()))) ?></p>
<?php endforeach ?>
<?php foreach ($item['events'] as $event): ?>
<?php
if ($event['is_join'])
{
  $eventIcon = 'icon_event_R.gif';
  $eventAlt  = '[参]';
}
else
{
  $eventIcon = 'icon_event_B.gif';
  $eventAlt  = '[イ]';
}
?>
<p class="event"><?php echo link_to(sprintf('<span class="icon">%s </span>%s', image_tag('/opCalendarPlugin/images/'.$eventIcon, array('alt' => $eventAlt)), op_truncate($event['name'], 40, '...', 1)), '@communityEvent_show?id='.$event['id']) ?></p>
<?php endforeach ?>
<?php foreach ($item['schedules'] as $schedule): ?>
<p class="schedule"><?php echo link_to(sprintf('<span class="icon">%s </span>%s', image_tag('/opCalendarPlugin/images/icon_pen.gif', array('alt' => '[予]')), op_truncate($schedule['title'], 40, '...', 1)), '@schedule_show?id='.$schedule['id']) ?></p>
<?php endforeach ?>
</td>
<?php endforeach ?>

</tr>
<?php endforeach ?>

</tbody>
</table>

<div class="block bottomBox">
<p class="pager"><?php echo link_to('&lt;&lt; 前の月', sprintf('@calendar_year_month?year=%d&month=%d', $ym['year_prev'], $ym['month_prev']), array('class' => 'prev')) ?>
 | <?php echo link_to('今月', '@calendar', array('class' => 'curr')) ?>
 | <?php echo link_to('次の月 &gt;&gt;', sprintf('@calendar_year_month?year=%d&month=%d', $ym['year_next'], $ym['month_next']), array('class' => 'next')) ?></p>
</div>

<div class="partsInfo">
<?php if ($add_schedule): ?>
<p class="note_schedule">※<?php echo image_tag('/opCalendarPlugin/images/icon_schedule.gif', array('alt' => '"予定を追加する"')) ?>をクリックすると予定を追加することができます。予定は他の人には公開されません。</p>
<?php endif ?>
<p class="note_birthday">※<?php echo image_tag('/opCalendarPlugin/images/icon_birthday.gif', array('alt' => '[誕]')) ?>は<?php echo $op_term['friend']->titleize() ?>の誕生日、<?php echo image_tag('/opCalendarPlugin/images/icon_event_B.gif', array('alt' => '[イ]')) ?>はイベント、<?php echo image_tag('/opCalendarPlugin/images/icon_event_R.gif', array('alt' => '[参]')) ?>は参加イベントを意味します。</p>
</div>
</div></div>
