<?php
$options = array();
$options['title'] = '予定を編集する';
$options['url']   = url_for('schedule_update', $schedule);
include_partial('formSchedule', array('form' => $form, 'options' => $options));
