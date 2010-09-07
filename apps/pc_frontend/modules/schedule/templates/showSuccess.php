<div id="formSchedule" class="dparts form"><div class="parts">
<div class="partsHeading"><h3>予定</h3></div>
<?php include_partial('detailScheduleTable', array('schedule' => $schedule)) ?>

<div class="operation">
<ul class="moreInfo button">
<li>
<form action="<?php echo url_for('schedule_edit', $schedule) ?>" method="get">
<input type="submit" class="input_submit" value="　編　集　" />
</form>
</li>
<li>
<form action="<?php echo url_for('schedule_delete_confirm', $schedule) ?>" method="get">
<input type="submit" class="input_submit" value="　削　除　" />
</form>
</li>
</ul>
</div>
</div></div>
