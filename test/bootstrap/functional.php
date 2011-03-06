<?php

if (!isset($app))
{
  $app = 'pc_frontend';
}

chdir(dirname(__FILE__).'/../../../..');
require_once 'config/ProjectConfiguration.class.php';
$configuration = ProjectConfiguration::getApplicationConfiguration($app, 'test', isset($debug) ? $debug : true);
sfContext::createInstance($configuration);
opToolkit::clearCache();
