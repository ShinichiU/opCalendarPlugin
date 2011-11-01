<?php use_javascript('/opCalendarPlugin/js/jquery-1.6.4.min.js', sfWebResponse::LAST) ?>
<?php use_javascript('/opCalendarPlugin/js/opCalendarPlugin', sfWebResponse::LAST) ?>
<?php use_stylesheet('/opCalendarPlugin/css/main') ?>
<div class="dparts weeklyCalendarTable" id="homeCalendarList_<?php echo $gadget->id ?>"><div class="parts">
<div class="partsHeading"><h3><?php echo __('Weekly Calendar') ?></h3></div>

<?php if ($isSelf): ?>
<div class="block formBox">
<?php echo $form->renderFormTag(url_for('@schedule_create_mini')), "\n" ?>
<?php echo $form->renderHiddenFields(), "\n" ?>
<label for="weekly_schedule_title"><?php echo __('Schedule') ?></label>
<?php echo $form['title']->render(array('class' => 'input_text')), "\n" ?>
<?php echo $form['start_date']->render(), "\n" ?>
<input type="hidden" value="<?php echo $w ?>" name="calendar_weekparam" />
<input type="submit" value="<?php echo __('Add') ?>" class="input_submit" />
<?php $html_id = sprintf('#homeCalendarList_%d', $gadget->id) ?>
<?php $url = url_for(sprintf('@homepage?calendar_weekparam=%d', $pw)) ?>
 <?php echo content_tag('a', '&lt;&lt;', array('href' => 'javascript:void(0)', 'onclick' => 'loadPage(\''.$url.'\', \''.$html_id.'\')', 'class' => 'prev', 'title' => __('Prev week'))) ?>
<?php $url = url_for('@homepage') ?>
 <?php echo content_tag('a', '■', array('href' => 'javascript:void(0)', 'onclick' => 'loadPage(\''.$url.'\', \''.$html_id.'\')', 'class' => 'prev', 'title' => __('This week'))) ?>
<?php $url = url_for(sprintf('@homepage?calendar_weekparam=%d', $nw)) ?>
 <?php echo content_tag('a', '&gt;&gt;', array('href' => 'javascript:void(0)', 'onclick' => 'loadPage(\''.$url.'\', \''.$html_id.'\')', 'class' => 'prev', 'title' => __('Prev week'))) ?>
</form>
</div>
<?php endif; ?>

<table class="calendar">
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
<li><?php echo link_to(__('Monthly Calendar'), 'calendar_member_obj', $member) ?></li>
</ul>
</div>
</div></div>
