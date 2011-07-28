<?php use_helper('opCalendar'); ?>
<?php
  $startTime = op_calendar_format_date($schedule->getStartTime(), 'XTime');
  $endTime = op_calendar_format_date($schedule->getEndTime(), 'XTime');
?>
<table class="detailScheduleTable">
<tbody>
<tr class="title"><th><?php echo __('Title') ?></th><td><?php echo $schedule->getTitle() ?> (<?php echo $schedule->getPublicFlagLabel() ?>)</td></tr>
<tr class="body"><th><?php echo __('Schedule author') ?></th><td><?php echo op_link_to_member($schedule->getMember()) ?></td></tr>
<tr class="start"><th><?php echo __('Start') ?></th><td><?php echo op_calendar_format_date($schedule->getStartDate(), 'XDate') ?> <?php echo $startTime ?></td></tr>
<tr class="end"><th><?php echo __('End') ?></th><td><?php echo op_calendar_format_date($schedule->getEndDate(), 'XDate') ?> <?php echo $endTime ?></td></tr>
<?php if ($schedule->getBody()): ?>
<tr class="body"><th><?php echo __('Detail') ?></th><td><?php echo nl2br($schedule->getBody()) ?></td></tr>
<?php endif ?>
<tr class="members"><th><?php echo __('Schedule member') ?></th>
<td>
<?php foreach($sf_data->getRaw('schedule')->getScheduleMembers() as $scheduleMember): ?>
<?php echo op_link_to_member($scheduleMember->Member) ?><br />
<?php endforeach; ?>
</td></tr>
<?php if (count($schedule->ScheduleResourceLocks)): ?>
<tr class="members"><th><?php echo __('Schedule resource lock') ?></th>
<td>
<?php foreach($schedule->ScheduleResourceLocks as $scheduleResourceLock): ?>
<?php echo $scheduleResourceLock->ScheduleResource->name ?><br />
<?php endforeach ?>
</td></tr>
<?php endif ?>
</tbody>
</table>
