<div class="dparts monthlyCalendarTable"><div class="parts">
<div class="partsHeading"><h3><?php echo format_number_choice('[0]%ym%|[1]%ym% of %f%', array('%ym%' => op_format_date(mktime(0, 0, 0, $ym['month_disp'], 1, $ym['year_disp']), 'XCalendarMonth'), '%f%' => $member->name), $isSelf ? 0 : 1) ?></h3></div>

<?php if ($isSelf && opConfig::get('op_calendar_google_data_api_is_active', false)): ?>
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
<?php if ($isSelf): ?>
<p class="moreInfo"><?php echo image_tag('/opCalendarPlugin/images/icon_schedule.gif', array('alt' => '')) ?> <?php echo link_to(__('Add schedule'), '@schedule_new') ?>
</p>
<?php endif; ?>
<p class="pager"><?php echo link_to('&lt;&lt; '.__('Prev month'), sprintf('@calendar_year_month_member_obj?id=%d&year=%d&month=%d', $member->id, $ym['year_prev'], $ym['month_prev']), array('class' => 'prev')) ?>
 | <?php echo link_to(__('This month'), '@calendar_member_obj?id='.$member->id, array('class' => 'curr')) ?>
 | <?php echo link_to(__('Next month').' &gt;&gt;', sprintf('@calendar_year_month_member_obj?id=%d&year=%d&month=%d', $member->id, $ym['year_next'], $ym['month_next']), array('class' => 'next')) ?></p>
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
<th class="mon"><?php echo __('Mon') ?></th>
<th class="tue"><?php echo __('Tue') ?></th>
<th class="wed"><?php echo __('Wed') ?></th>
<th class="thu"><?php echo __('Thu') ?></th>
<th class="fri"><?php echo __('Fri') ?></th>
<th class="sat"><?php echo __('Sat') ?></th>
<th class="sun"><?php echo __('Sun') ?></th>
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
<?php if ($isSelf && $add_schedule): ?>
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
<p class="pager"><?php echo link_to('&lt;&lt; '.__('Prev month'), sprintf('@calendar_year_month_member_obj?id=%d&year=%d&month=%d', $member->id, $ym['year_prev'], $ym['month_prev']), array('class' => 'prev')) ?>
 | <?php echo link_to(__('This month'), '@calendar_member_obj?id='.$member->id, array('class' => 'curr')) ?>
 | <?php echo link_to(__('Next month').' &gt;&gt;', sprintf('@calendar_year_month_member_obj?id=%d&year=%d&month=%d', $member->id, $ym['year_next'], $ym['month_next']), array('class' => 'next')) ?></p>
</div>

<?php if ($isSelf): ?>
<div class="partsInfo">
<?php if ($add_schedule): ?>
<p class="note_schedule">※<?php echo __('You can add schedule to click %img%.', array('%img%' => image_tag('/opCalendarPlugin/images/icon_schedule.gif', array('alt' => __('Add schedule'))))) ?></p>
<?php endif ?>
<p class="note_birthday">※<?php echo __('%img1% stands for %f%\'s birthday, %img2% stands for event and %img3% stands for joined event.', array('%img1%'=>image_tag('/opCalendarPlugin/images/icon_birthday.gif', array('alt' => '[誕]')), '%f%' => $op_term['friend']->titleize(), '%img2%' => image_tag('/opCalendarPlugin/images/icon_event_B.gif', array('alt' => '[イ]')), '%img3%' => image_tag('/opCalendarPlugin/images/icon_event_R.gif', array('alt' => '[参]')))) ?></p>
</div>
<?php endif ?>
</div></div>
