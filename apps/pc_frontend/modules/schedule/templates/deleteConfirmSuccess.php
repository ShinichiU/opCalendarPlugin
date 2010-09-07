<div id="formSchedule" class="dparts form"><div class="parts">
<div class="partsHeading"><h3>この予定を削除してよろしいですか？</h3></div>
<?php include_partial('detailScheduleTable', array('schedule' => $schedule)) ?>

<div class="operation">
<ul class="moreInfo button">
<li>
<form action="<?php echo url_for('schedule_delete', $schedule) ?>" method="post">
<?php echo $form->renderHiddenFields(), "\n" ?>
<input type="submit" class="input_submit" value="　削　除　" />
</form>
</li>
<li>
<form action="<?php echo url_for('schedule_show', $schedule) ?>" method="get">
<input type="submit" class="input_submit" value="キャンセル" />
</form>
</li>
</ul>
</div>
</div></div>
