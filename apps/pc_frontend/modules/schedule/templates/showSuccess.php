<div id="formSchedule" class="dparts form"><div class="parts">
<div class="partsHeading"><h3><?php echo __('Schedule') ?></h3></div>
<?php include_partial('detailScheduleTable', array('schedule' => $schedule)) ?>

<?php if ($schedule->isEditable($sf_user->getMemberId())): ?>
<div class="operation">
<ul class="moreInfo button">
<li>
<form action="<?php echo url_for('schedule_edit', $schedule) ?>" method="get">
<input type="submit" class="input_submit" value="<?php echo __('Edit') ?>" />
</form>
</li>
<li>
<form action="<?php echo url_for('schedule_delete_confirm', $schedule) ?>" method="get">
<input type="submit" class="input_submit" value="<?php echo __('Delete') ?>" />
</form>
</li>
</ul>
</div>
<?php endif; ?>
</div></div>
