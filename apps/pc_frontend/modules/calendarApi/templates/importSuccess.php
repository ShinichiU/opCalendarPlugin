<div id="Top"><div class="parts googleCalendarDescBox" style="position: relative;">
<div class="body">
<ul>
<li><?php echo __('Schedules fetched from Google are saved and updated in this SNS.') ?></li>
<li><?php echo __('If the public flag was set to [All Members], those schedules are public to SNS members.') ?></li>
<li><?php echo __('If the public flag was set to [Participants only scheduled public], those schedules can see the members that have the mail address related to Google Calendar and invited to schedules on Google Calendar. Thus, the all SNS member cannot those schedules.') ?> </li>
<li><?php echo __('Then, the schedules you create are the only target of convert.') ?> </li>
<?php if ($form->isNeedIsSaveEmail()): ?>
<li><?php echo __('If [Save email on Google Calendar] is marked, the schedules on Google calendar can relate even if the email on SNS is defferent to the email on Google Calendar.') ?></li>
<li><?php echo __('[Save email on Google Calendar] is to be unmark if the email on Google Calendar didn\'t want to save in SNS.') ?></li>
<?php endif ?>
</ul>
</div>
</div><!-- parts --></div>

<?php
$options = array(
  'button' => __('Update'),
  'title' => __('Select to fetch Google Calendar'),
  'url' => url_for('calendar_api_import')
);

op_include_form('googleCalendarImportForm', $form, $options);
?>
