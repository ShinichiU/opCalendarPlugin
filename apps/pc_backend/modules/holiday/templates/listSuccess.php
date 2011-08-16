<?php slot('title', __('Holiday settings')); ?>

<h3><?php echo __('Holiday settings'); ?></h3>

<p><?php echo __('Please set holiday name and the day.'); ?><br />※<?php echo __('It would make holiday repeat every year if the year was empty.')?></p>

<table>
<thead>
<tr>
<th><?php echo __('Holiday name'); ?></th>
<th><?php echo __('Holiday'); ?></th>
<th colspan="2"><?php echo __('Operation'); ?></th>
</tr>
</thead>
<tbody>
<?php foreach ($activeForms as $activeForm): ?>
<tr>
<?php echo $activeForm->renderFormTag(url_for('holiday_update', $activeForm->getObject())) ?>
<td>
<?php echo $activeForm->renderHiddenFields() ?>
<?php echo $activeForm['name']->render() ?></td>
<td><?php echo $activeForm['year']->render(array('size' => 4)) ?><?php echo __('Year'); ?> <?php echo $activeForm['month']->render() ?> <?php echo $activeForm['day']->render() ?></td>
<td><input type="submit" value="<?php echo __('Update'); ?>" /></td>
</form>
<td>
<?php echo $form->renderFormTag(url_for('holiday_delete', $activeForm->getObject())) ?>
<?php echo $form ?>
<input type="submit" value="<?php echo __('Delete'); ?>" />
</form>
</td>
</tr>
<?php endforeach; ?>
<tr>
<?php echo $newForm->renderFormTag(url_for('@holiday_create')) ?>
<?php echo $newForm->renderHiddenFields() ?>
<td><?php echo $newForm['name']->render() ?></td>
<td><?php echo $newForm['year']->render(array('size' => 4)) ?><?php echo __('Year'); ?> <?php echo $newForm['month']->render() ?> <?php echo $newForm['day']->render() ?></td>
<td colspan="2">
<input type="submit" value="<?php echo __('Add item'); ?>" />
</td>
</form>
</tr>
</tbody>
</table>
