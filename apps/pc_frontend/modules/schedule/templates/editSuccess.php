<?php
$options = array();
$options['title'] = __('Edit schedule');
$options['url']   = url_for('schedule_update', $schedule);
include_partial('formSchedule', array('form' => $form, 'options' => $options));
