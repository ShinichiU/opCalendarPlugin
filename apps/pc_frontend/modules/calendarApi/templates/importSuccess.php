<div id="Top"><div class="parts googleCalendarDescBox" style="position: relative;">
<div class="body">
<ul>
<li>Googleから取得したスケジュールをSNSにも保存、更新します。</li>
<li>[全員に公開]に設定した場合は、Googleカレンダーの公開範囲に関わらず、SNSのメンバー全員に公開されます。</li>
<li>[スケジュール参加者のみに公開]に設定した場合、Googleカレンダーの招待先のメールアドレスがSNSに登録されていない場合、関連付けがされませんのですべてのスケジュール参加者に表示されるものではありません。</li>
<li>自分が作成したスケジュールのみスケジュールコンバート対象となります。</li>
<?php if ($form->isNeedIsSaveEmail()): ?>
<li>[Google Calendar のemailの保存]をすることで、Googleカレンダーで使っているemailとSNSで使っているemailが違う場合でも他のメンバーの作成したスケジュールの参加者として紐付けが可能になります。</li>
<li>SNS側にGoogleカレンダーのemailを保存したくない場合は[Google Calendar のemailの保存]のチェックボックスをオフにしてください。</li>
<?php endif ?>
</ul>
</div>
</div><!-- parts --></div>

<?php
$options = array(
  'button' => __('Update'),
  'title' => __('読み込むGoogleカレンダーを選択'),
  'url' => url_for('calendar_api_import')
);

op_include_form('googleCalendarImportForm', $form, $options);
?>
