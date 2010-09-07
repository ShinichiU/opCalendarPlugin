<?php
$options = array();
$options['title'] = '予定を追加する';
$options['url']   = url_for('@schedule_create');
include_partial('formSchedule', array('form' => $form, 'options' => $options));
