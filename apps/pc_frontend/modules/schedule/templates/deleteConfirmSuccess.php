<div id="formSchedule" class="dparts form"><div class="parts">
<div class="partsHeading"><h3><?php echo __('Are you sure to delete this schedule?')?></h3></div>
<?php include_partial('detailScheduleTable', array('schedule' => $schedule)) ?>

<div class="operation">
<ul class="moreInfo button">
<li>
<form action="<?php echo url_for('schedule_delete', $schedule) ?>" method="post">
<?php echo $form->renderHiddenFields(), "\n" ?>
<input type="submit" class="input_submit" value="<?php echo __('Delete') ?>" />
</form>
</li>
<li>
<form action="<?php echo url_for('schedule_show', $schedule) ?>" method="get">
<input type="submit" class="input_submit" value="<?php echo __('Cancel ') ?>" />
</form>
</li>
</ul>
</div>
</div></div>
