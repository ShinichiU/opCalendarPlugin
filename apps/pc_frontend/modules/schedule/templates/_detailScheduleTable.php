<?php
  $startTime = op_format_date($schedule->getStartTime(), 'HH時mm分');
  if(!$startTime) $startTime = '--時--分';
  $endTime = op_format_date($schedule->getEndTime(), 'HH時mm分');
  if(!$endTime) $endTime = '--時--分';
?>
<table class="detailScheduleTable">
<tbody>
<tr class="title"><th>タイトル</th><td><?php echo $schedule->getTitle() ?></td></tr>
<tr class="start"><th>開始</th><td><?php echo op_format_date($schedule->getStartDate(), 'yyyy年MM月dd日') ?> <?php echo $startTime ?></td></tr>
<tr class="end"><th>終了</th><td><?php echo op_format_date($schedule->getEndDate(), 'yyyy年MM月dd日') ?> <?php echo $endTime ?></td></tr>
<tr class="body"><th>詳細</th><td><?php echo nl2br($schedule->getBody()) ?></td></tr>
</tbody>
</table>
