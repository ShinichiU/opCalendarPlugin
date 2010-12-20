<?php slot('submenu') ?>
<?php include_partial('plugin/submenu') ?>
<?php end_slot(); ?>
<h2><?php echo __('アプリケーションプラグイン設定') ?></h2>
<h3><?php echo __('カレンダープラグイン設定') ?></h3>

<h4>スケジュールリソース削除確認</h4>

<?php echo $form->renderFormTag(url_for('opCalendarPlugin_resource_delete', $scheduleResource), array('method' => 'delete')) ?>
<table>
<thead>
<tr>
<th>リソース名</th>
<th>説明</th>
<th>リソースタイプ名</th>
<th>リソース数</th>
</tr>
</thead>
<tbody>
<tr>
<td>
<?php echo $scheduleResource->name ?>
</td><td>
<?php echo nl2br($scheduleResource->description) ?>
</td><td>
<?php echo $scheduleResource->ResourceType->name ?>
</td><td>
<?php echo $scheduleResource->resource_limit ?>
</td>
</tr>
</tbody>
</table>
<?php echo $form->renderHiddenFields() ?>
<input type="submit" value=" 削 除 " />
</form>
