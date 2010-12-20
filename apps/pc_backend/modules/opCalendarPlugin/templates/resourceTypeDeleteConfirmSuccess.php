<?php slot('submenu') ?>
<?php include_partial('plugin/submenu') ?>
<?php end_slot(); ?>
<h2><?php echo __('アプリケーションプラグイン設定') ?></h2>
<h3><?php echo __('カレンダープラグイン設定') ?></h3>

<h4>スケジュールリソースタイプ削除確認</h4>

<?php echo $form->renderFormTag(url_for('opCalendarPlugin_resource_type_delete', $resourceType), array('method' => 'delete')) ?>
<table>
<thead>
<tr>
<th>リソースタイプ名</th>
<th>説明</th>
</tr>
</thead>
<tbody>
<tr>
<td>
<?php echo $resourceType->name ?>
</td><td>
<?php echo nl2br($resourceType->description) ?>
</td>
</tr>
</tbody>
</table>
<?php echo $form->renderHiddenFields() ?>
<input type="submit" value=" 削 除 " />
</form>
