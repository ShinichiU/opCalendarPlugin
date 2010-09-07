<?php slot('title', '祝日設定'); ?>

<h3>祝日設定</h3>

<p>祝日名称と祝日を設定してください。</p>

<table>
<thead>
<tr>
<th>祝日名称</th>
<th>祝日</th>
<th colspan="2">操作</th>
</tr>
</thead>
<tbody>
<?php foreach ($activeForms as $activeForm): ?>
<tr>
<?php echo $activeForm->renderFormTag(url_for('holiday_update', $activeForm->getObject())) ?>
<td>
<?php echo $activeForm->renderHiddenFields() ?>
<?php echo $activeForm['name']->render() ?></td>
<td><?php echo $activeForm['month']->render() ?> <?php echo $activeForm['day']->render() ?></td>
<td><input type="submit" value="変更" /></td>
</form>
<td>
<?php echo $form->renderFormTag(url_for('holiday_delete', $activeForm->getObject())) ?>
<?php echo $form ?>
<input type="submit" value="削除" />
</form>
</td>
</tr>
<?php endforeach; ?>
<tr>
<?php echo $newForm->renderFormTag(url_for('@holiday_create')) ?>
<?php echo $newForm->renderHiddenFields() ?>
<td><?php echo $newForm['name']->render() ?></td>
<td><?php echo $newForm['month']->render() ?> <?php echo $newForm['day']->render() ?></td>
<td colspan="2">
<input type="submit" value="項目追加" />
</td>
</form>
</tr>
</tbody>
</table>
