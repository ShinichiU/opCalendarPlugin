<tr><td class="community_calendar" colspan="2">
<?php use_stylesheet('/opCalendarPlugin/css/main') ?>
<div class="block">
<span class="pager">
<?php $community_home_uri = '@community_home?id='.$community->id ?>
 <?php echo link_to('&lt;&lt;', $community_home_uri.'&calendar_weekparam='.$pw, array('class' => 'prev', 'title' => __('Prev week'))), "\n" ?>
 <?php echo link_to('■', $community_home_uri, array('class' => 'curr', 'title' => __('This week')), "\n") ?>
 <?php echo link_to('&gt;&gt;', $community_home_uri.'&calendar_weekparam='.$nw , array('class' => 'next', 'title' => __('Next week'))), "\n" ?>
</span>
</div>

<table class="community_calendar calendar">
<tbody><tr>
<?php foreach ($calendar as $item): ?>

<?php
$cls_today = $item['today'] ? ' today' : '';
$cls_holiday = count($item['holidays']) ? ' holiday' : '';

$str_month = '';
if (1 == $item['day'] || 'mon' === $item['dayofweek_class_name'])
{
   $str_month = sprintf('%d/', $item['month']);
}
?>
<?php echo sprintf('<td class="%s%s%s">', $item['dayofweek_class_name'], $cls_today, $cls_holiday), "\n" ?>
<?php echo sprintf('<p class="day"><span class="date">%s%d</span> <span class="day">(%s)</span></p>', $str_month, $item['day'], __($item['dayofweek_item_name'])), "\n" ?>
<?php foreach ($item['holidays'] as $holiday): ?>
<p class="holiday"><?php echo $holiday ?></p>
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
<p class="event"><?php echo link_to(sprintf('<span class="icon">%s </span>%s', image_tag('/opCalendarPlugin/images/'.$eventIcon, array('alt' => $eventAlt)), op_truncate($event['name'], 20, '...', 1)), '@communityEvent_show?id='.$event['id']) ?></p>
<?php endforeach ?>
<?php foreach ($item['schedules'] as $schedule): ?>
<?php if ($schedule->isShowable($sf_user->getMemberId())): ?>
<p class="schedule"><?php echo link_to(sprintf('<span class="icon">%s </span>%s', image_tag('/opCalendarPlugin/images/icon_pen.gif', array('alt' => '[予]')), op_truncate($schedule->title, 20, '...', 1)), '@schedule_show?id='.$schedule->id) ?></p>
<?php endif ?>
<?php endforeach ?>
</td>
<?php endforeach ?>

</tr>
</tbody></table>

<div class="block moreInfo">
<ul class="moreInfo">
<li><?php echo link_to(__('Monthly Calendar'), 'calendar_community_obj', $community) ?></li>
</ul>
</div>
</div></div>

</td></tr>
