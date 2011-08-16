<table class="inputScheduleTable">
<tr class="title">
<th><label for="schedule_title"><?php echo __('Title') ?> <strong>*</strong></label></th>
<td><?php if ($form['title']->hasError()): ?><?php echo $form['title']->renderError() ?><?php endif; ?>
<?php echo $form['title']->render(array('class' => 'input_text')) ?></td>
</tr>
<tr class="start">
<th><label for="schedule_start_date"><?php echo __('Start') ?>  <strong>*</strong></label></th>
<td><?php if ($form['start_date']->hasError()): ?><?php echo $form['start_date']->renderError() ?>
<?php elseif ($form['start_time']->hasError()): ?><?php echo $form['start_time']->renderError() ?><?php endif; ?>
<?php echo $form['start_date']->render() ?> <?php echo $form['start_time']->render() ?></td>
</tr>
<tr class="end">
<th><label for="schedule_end_date"><?php echo __('End') ?>  <strong>*</strong></label></th>
<td><?php if ($form['end_date']->hasError()): ?><?php echo $form['end_date']->renderError() ?>
<?php elseif ($form['end_time']->hasError()): ?><?php echo $form['end_time']->renderError() ?><?php endif; ?>
<?php echo $form['end_date']->render() ?> <?php echo $form['end_time']->render() ?></td>
</tr>
<tr class="body">
<th><label for="schedule_body"><?php echo __('Detail') ?> </label></th>
<td><?php echo $form['body']->render() ?></td>
</tr>
<tr class="public_flag">
<th><label for="schedule_public_flag"><?php echo __('Public flag') ?>  <strong>*</strong></label></th>
<td><?php echo $form['public_flag']->render() ?></td>
</tr>
<tr class="schedule_member">
<th><label for="schedule_schedule_member"><?php echo __('Schedule member') ?>  <strong>*</strong></label></th>
<td><?php echo $form['schedule_member']->render() ?></td>
</tr>
<?php $max = (int)sfConfig::get('app_schedule_resource_list_max', 5) ?>
<?php for ($i = 1; $i <= $max; $i++): ?>
<tr class="schedule_resource_lock" id="schedule_resource_lock_<?php echo $i ?>">
<th><label for="schedule_schedule_resource_lock_<?php echo $i ?>_id"><?php echo __('Schedule resource lock%1%', array('%1%' => $i)) ?></label></th>
<td><?php echo $form['schedule_resource_lock_'.$i]->render() ?></td>
</tr>
<?php endfor ?>
</table>
