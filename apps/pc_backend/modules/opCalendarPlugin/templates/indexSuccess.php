<?php slot('submenu') ?>
<?php include_partial('plugin/submenu') ?>
<?php end_slot(); ?>
<h2><?php echo __('アプリケーションプラグイン設定') ?></h2>
<h3><?php echo __('カレンダープラグイン設定') ?></h3>

<h4>スケジュールリソースタイプ</h4>

<table>
<thead>
<tr>
<th>リソースタイプ名</th>
<th>説明</th>
<th colspan="2">操作</th>
</tr>
</thead>
<tbody>
<?php foreach ($resourceTypeForms as $resourceTypeForm): ?>
<tr>
<?php echo $resourceTypeForm->renderFormTag(url_for('opCalendarPlugin_resource_type_update', $resourceTypeForm->getObject()), array('method' => 'put')) ?>
<td>
<?php echo $resourceTypeForm['name']->render() ?>
</td><td>
<?php echo $resourceTypeForm['description']->render() ?>
</td><td>
<?php echo $resourceTypeForm->renderHiddenFields() ?>
<input type="submit" value=" 更 新 " />
</td><td>
</form>
<?php echo $resourceTypeForm->renderFormTag(url_for('opCalendarPlugin_resource_type_delete_confirm', $resourceTypeForm->getObject()), array('method' => 'get')) ?>
<input type="submit" value=" 削 除 確 認 " />
</td>
</form>
</tr>
<?php endforeach ?>
<tr>
<?php echo $newResourceTypeForm->renderFormTag(url_for('@opCalendarPlugin_resource_type_create'), array('method' => 'post')) ?>
<td>
<?php echo $newResourceTypeForm['name']->render() ?>
</td><td>
<?php echo $newResourceTypeForm['description']->render() ?>
</td><td>
<?php echo $newResourceTypeForm->renderHiddenFields() ?>
<input type="submit" value=" 作 成 " />
</td>
</form>
<td></td>
</tr>
</tbody>
</table>

<h4>スケジュールリソース</h4>

<table>
<thead>
<tr>
<th>リソース名</th>
<th>説明</th>
<th>リソースタイプ</th>
<th>リソース数(整数値)</th>
<th colspan="2">操作</th>
</tr>
</thead>
<tbody>
<?php foreach ($scheduleResourceForms as $scheduleResourceForm): ?>
<tr>
<?php echo $scheduleResourceForm->renderFormTag(url_for('opCalendarPlugin_resource_update', $scheduleResourceForm->getObject()), array('method' => 'put')) ?>
<td>
<?php echo $scheduleResourceForm['name']->render() ?>
</td><td>
<?php echo $scheduleResourceForm['description']->render() ?>
</td><td>
<?php echo $scheduleResourceForm['resource_type_id']->render() ?>
</td><td>
<?php echo $scheduleResourceForm['resource_limit']->render() ?>
</td><td>
<?php echo $scheduleResourceForm->renderHiddenFields() ?>
<input type="submit" value=" 更 新 " />
</td><td>
</form>
<?php echo $scheduleResourceForm->renderFormTag(url_for('opCalendarPlugin_resource_delete_confirm', $scheduleResourceForm->getObject()), array('method' => 'get')) ?>
<input type="submit" value=" 削 除 確 認 " />
</td>
</form>
</tr>
<?php endforeach ?>
<tr>
<?php echo $newScheduleResourceForm->renderFormTag(url_for('@opCalendarPlugin_resource_create'), array('method' => 'post')) ?>
<td>
<?php echo $newScheduleResourceForm['name']->render() ?>
</td><td>
<?php echo $newScheduleResourceForm['description']->render() ?>
</td><td>
<?php echo $newScheduleResourceForm['resource_type_id']->render() ?>
</td><td>
<?php echo $newScheduleResourceForm['resource_limit']->render() ?>
</td><td>
<?php echo $newScheduleResourceForm->renderHiddenFields() ?>
<input type="submit" value=" 作 成 " />
</td>
</form>
<td></td>
</tr>
</tbody>
</table>
