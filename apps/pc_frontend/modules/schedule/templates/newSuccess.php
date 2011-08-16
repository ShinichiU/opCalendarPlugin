<?php
$options = array();
$options['title'] = __('Add schedule');
$options['url']   = url_for('@schedule_create');
include_partial('formSchedule', array('form' => $form, 'options' => $options));
