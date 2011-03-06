<?php
if (!isset($prefix))
{
  $prefix = '';
}
if (!isset($app))
{
  $app = 'pc_frontend';
}

$configuration = ProjectConfiguration::getApplicationConfiguration($app, 'test', isset($debug) ? $debug : true);
new sfDatabaseManager($configuration);
$task = new sfDoctrineBuildTask($configuration->getEventDispatcher(), new sfFormatter());
$task->setConfiguration($configuration);
$task->run(array(), array(
  'no-confirmation' => true,
  'db'              => true,
  'and-load'        => true,
  'application'     => $app,
  'env'             => 'test',
));

$task = new sfDoctrineDataLoadTask($configuration->getEventDispatcher(), new sfFormatter());
$task->setConfiguration($configuration);
$task->run(dirname(__FILE__).'/../fixtures'.$prefix);
