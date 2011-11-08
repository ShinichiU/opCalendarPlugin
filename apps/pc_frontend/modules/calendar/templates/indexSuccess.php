<?php use_helper('opCalendar') ?>
<?php use_javascript('/opCalendarPlugin/js/jquery-1.6.4.min.js', sfWebResponse::LAST) ?>
<?php use_javascript('/opCalendarPlugin/js/opCalendarPlugin', sfWebResponse::LAST) ?>
<div class="dparts monthlyCalendarTable"><div class="parts">
<div class="partsHeading"><h3><?php $is_community ? printf('[%s] ', $community->name) : '' ?><?php echo format_number_choice('[0]%ym%|[1]%ym% of %f%', array('%ym%' => op_format_date(mktime(0, 0, 0, $ym['month_disp'], 1, $ym['year_disp']), 'XCalendarMonth'), '%f%' => $member->name), $is_community ? 1 : $isSelf ? 0 : 1) ?></h3></div>

<?php if (!$is_community && $isSelf && opConfig::get('op_calendar_google_data_api_is_active', false)): ?>
<div class="block topBox">
<p class="note_schedule">
<?php if (opGoogleCalendarOAuth::getInstance()->isNeedRedirection()): ?>
&nbsp;<?php echo link_to(__('Enable to Google Calendar\'s permission settings'), '@calendar_api') ?>
<?php else: ?>
&nbsp;<?php echo link_to(__('Add schedule to Google Calendar'), '@calendar_api_import') ?>
<?php endif ?>
</p>
</div>
<?php endif ?>
<div class="block topBox">
<?php if (!$is_community && $isSelf): ?>
<p class="moreInfo"><?php echo image_tag('/opCalendarPlugin/images/icon_schedule.gif', array('alt' => '')) ?> <?php echo link_to(__('Add schedule'), '@schedule_new') ?>
</p>
<?php endif; ?>
<?php slot('calendar_pager') ?>
<?php
$obj_route = $is_community ? '@calendar_community_obj' : '@calendar_member_obj';
$obj_route_year_month = $is_community ? '@calendar_year_month_community_obj' : '@calendar_year_month_member_obj';
$link_to_id = $is_community ? $community->id : $member->id;
?>
<p class="pager">
<?php $class = '.monthlyCalendarTable' ?>
<?php $url = url_for(sprintf('%s?id=%d&year=%d&month=%d', $obj_route_year_month, $link_to_id, $ym['year_prev'], $ym['month_prev'])) ?>
<?php echo op_ajax_link_calendar('&lt;&lt; '.__('Prev month'), $url, $class, array('class' => 'prev')) ?>
<?php $url = url_for(sprintf('%s?id=%d', $obj_route, $link_to_id)) ?>
 | <?php echo op_ajax_link_calendar(__('This month'), $url, $class, array('class' => 'curr')) ?>
<?php $url = url_for(sprintf('%s?id=%d&year=%d&month=%d', $obj_route_year_month, $link_to_id, $ym['year_next'], $ym['month_next'])) ?>
 | <?php echo op_ajax_link_calendar(__('Next month').' &gt;&gt;', $url, $class, array('class' => 'next')) ?>
<?php end_slot() ?>
<?php include_slot('calendar_pager') ?>
</div>

<table class="calendar">
<?php for ($i = 0; $i < 7; $i++): ?>
<colgroup class="<?php echo $dayofweek['class'][$i] ?>"></colgroup>
<?php endfor ?>
<thead>
<tr>
<?php for ($i = 0; $i < 7; $i++): ?>
<th class="<?php echo $dayofweek['class'][$i] ?>"><?php echo __($dayofweek['item'][$i]) ?></th>
<?php endfor ?>
</tr>
</thead>
<tbody>
<?php foreach ($calendar as $week): ?>

<tr>
<?php foreach ($week as $item): ?>

<?php
if (!isset($item['day']))
{
  echo sprintf('<td class="%s empty"></td>', $item['dayofweek_class_name']), "\n";
  continue;
}

$cls_today = $item['today'] ? ' today' : '';
$cls_holiday = count($item['holidays']) ? ' holiday' : '';
?>
<?php echo sprintf('<td class="%s%s%s">', $item['dayofweek_class_name'], $cls_today, $cls_holiday), "\n" ?>
<p class="day"><span class="date"><?php echo __($item['day']) ?></span></p>
<?php if (!$is_community && $isSelf && $add_schedule): ?>
<p class="new_schedule"><?php echo link_to(image_tag('/opCalendarPlugin/images/icon_schedule.gif', array('alt' => __('Add schedule'))), '@schedule_new_for_this_date?year='.$ym['year_disp'].'&month='.$ym['month_disp'].'&day='.$item['day']) ?></p>
<?php endif ?>
<?php foreach ($item['holidays'] as $holiday): ?>
<p class="holiday"><?php echo $holiday ?></p>
<?php endforeach ?>
<?php foreach ($item['births'] as $birth_member): ?>
<p class="birthday"><?php echo op_link_to_member($birth_member, array('link_target' => sprintf('<span class="icon">%s </span>%sさん', image_tag('/opCalendarPlugin/images/icon_birthday.gif', array('alt' => '[誕]')), $birth_member->getName()))) ?></p>
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
<?php if ($schedule->isShowable($sf_user->getMemberId())): ?>
<p class="schedule"><?php echo link_to(sprintf('<span class="icon">%s </span>%s', image_tag('/opCalendarPlugin/images/icon_pen.gif', array('alt' => '[予]')), op_truncate($schedule->title, 40, '...', 1)), '@schedule_show?id='.$schedule->id) ?></p>
<?php endif ?>
<?php endforeach ?>
</td>
<?php endforeach ?>

</tr>
<?php endforeach ?>

</tbody>
</table>

<div class="block bottomBox">
<?php include_slot('calendar_pager') ?>
</div>

<?php if ($is_community || $isSelf): ?>
<div class="partsInfo">
<?php if ($add_schedule): ?>
<p class="note_schedule">※<?php echo __('You can add schedule to click %img%.', array('%img%' => image_tag('/opCalendarPlugin/images/icon_schedule.gif', array('alt' => __('Add schedule'))))) ?></p>
<?php endif ?>
<p class="note_birthday">※<?php echo __('%img1% stands for %f%\'s birthday, %img2% stands for event and %img3% stands for joined event.', array('%img1%'=>image_tag('/opCalendarPlugin/images/icon_birthday.gif', array('alt' => '[誕]')), '%f%' => $op_term['friend']->titleize(), '%img2%' => image_tag('/opCalendarPlugin/images/icon_event_B.gif', array('alt' => '[イ]')), '%img3%' => image_tag('/opCalendarPlugin/images/icon_event_R.gif', array('alt' => '[参]')))) ?></p>
</div>
<?php endif ?>
</div></div>
