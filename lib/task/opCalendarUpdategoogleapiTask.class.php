<?php

class opCalendarUpdategoogleapiTask extends sfBaseTask
{
  protected $opCalendarOAuth;

  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'pc_frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('interval', null, sfCommandOption::PARAMETER_REQUIRED, 'api interval time (second)', 0),
    ));

    $this->namespace        = 'opCalendar';
    $this->name             = 'update-google-api';
    $this->briefDescription = '';
    $this->detailedDescription = <<<EOF
The [opCalendar:update-google-api|INFO] task does things.
Call it with:

  [php symfony opCalendar:update-google-api|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();
    sfContext::createInstance($this->createConfiguration($options['application'], $options['env']), $options['application']);

    if (!opConfig::get('op_calendar_google_data_api_auto_update', false))
    {
      throw new sfException('This task is not allowed. Please allow from "pc_backend.php/opCalendarPlugin" setting.');
    }

    $crons = opCalendarPluginToolkit::getAllGoogleCalendarCronConfig();
    if (!count($crons))
    {
      $this->logSection('end', 'No cron data');

      return true;
    }

    $this->opCalendarOAuth = opCalendarOAuth::getInstance();

    $this->logSection('prepared', 'start update');

    foreach ($crons as $cron)
    {
      $this->updateMemberSchedule($cron, (int)$options['interval']);
    }
  }

  protected function updateMemberSchedule($cron, $interval)
  {
    $memberId = $cron['member_id'];
    $member = Doctrine_Core::getTable('Member')->find($memberId);
    if (!$member || !$this->opCalendarOAuth->authenticate($member))
    {
      return false;
    }

    $cronConfig = unserialize($cron['serial']);
    foreach ($cronConfig['src'] as $id)
    {
      $this->logSection('prepare', sprintf('update member_id: %d, id: %s', $memberId, $id));
      if (!$events = $this->getContents($id))
      {
        $this->logSection('result', 'skipped');

        continue;
      }

      $isSuccess = opCalendarPluginToolkit::insertSchedules($events, $cronConfig['public_flag'], true, $member);
      $this->logSection('result', $isSuccess ? 'success' : 'failed');

      if ($interval)
      {
        $this->logSection('sleep', 'interval: '.$interval.' second');

        sleep($interval);
      }
    }
  }

  protected function getContents($id)
  {
    $calendar = new Google_Service_Calendar($this->opCalendarOAuth->getClient());
    $lastDay = opCalendarPluginToolkit::getLastDay(date('m', strtotime('+1 month')));

    return $calendar->events->listEvents($id, array(
      'timeMin' => date('c', strtotime(sprintf('%s-01 00:00:00', date('Y-m', strtotime('-1 month'))))),
      'timeMax' => date('c', strtotime(sprintf('%s-%02d 23:59:59', date('Y-m', strtotime('+1 month')), $lastDay))),
    ));
  }
}
