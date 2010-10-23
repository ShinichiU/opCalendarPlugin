<table class="inputScheduleTable">
<tr class="title">
<th><label for="schedule_title">タイトル <strong>*</strong></label></th>
<td><?php if ($form['title']->hasError()): ?><?php echo $form['title']->renderError() ?><?php endif; ?>
<?php echo $form['title']->render(array('class' => 'input_text')) ?></td>
</tr>
<tr class="start">
<th><label for="schedule_start_date">開始 <strong>*</strong></label></th>
<td><?php if ($form['start_date']->hasError()): ?><?php echo $form['start_date']->renderError() ?>
<?php elseif ($form['start_time']->hasError()): ?><?php echo $form['start_time']->renderError() ?><?php endif; ?>
<?php echo $form['start_date']->render() ?> <?php echo $form['start_time']->render() ?></td>
</tr>
<tr class="end">
<th><label for="schedule_end_date">終了 <strong>*</strong></label></th>
<td><?php if ($form['end_date']->hasError()): ?><?php echo $form['end_date']->renderError() ?>
<?php elseif ($form['end_time']->hasError()): ?><?php echo $form['end_time']->renderError() ?><?php endif; ?>
<?php echo $form['end_date']->render() ?> <?php echo $form['end_time']->render() ?></td>
</tr>
<tr class="body">
<th><label for="schedule_body">詳細</label></th>
<td><?php echo $form['body']->render() ?></td>
</tr>
<tr class="public_flag">
<th><label for="schedule_public_flag">公開範囲 <strong>*</strong></label></th>
<td><?php echo $form['public_flag']->render() ?></td>
</tr>
<tr class="schedule_member">
<th><label for="schedule_schedule_member">参加メンバー <strong>*</strong></label></th>
<td><?php echo $form['schedule_member']->render() ?></td>
</tr>
</table>
