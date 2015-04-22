<?php

class opCalendarUpdategoogleapiTask extends sfBaseTask
{
  protected $opCalendarOAuth;

  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'pc_frontend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod'),
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
    new sfDatabaseManager($this->configuration);

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
    if (!$member || !($calendar = $this->opCalendarOAuth->getCalendar($member)))
    {
      return false;
    }

    $id = $calendar->calendars->get('primary')->id;
    $publicFlag = $cron['public_flag'];

    $this->logSection('prepare', sprintf('update member_id: %d, id: %s', $memberId, $id));
    if (!$events = $this->getContents($id, $calendar))
    {
      $this->logSection('result', 'skipped');

      continue;
    }

    $isSuccess = opCalendarPluginToolkit::insertSchedules($events, $publicFlag, $member);
    $this->logSection('result', $isSuccess ? 'success' : 'failed');

    if ($interval)
    {
      $this->logSection('sleep', 'interval: '.$interval.' second');

      sleep($interval);
    }
  }

  protected function getContents($id, Google_Service_Calendar $calendar)
  {
    $endYear = date('Y', strtotime('+1 month'));
    $endMonth = date('m', strtotime('+1 month'));
    $endDay = opCalendarPluginToolkit::getLastDay($endMonth, $endYear);

    return $calendar->events->listEvents($id, array(
      'timeMin' => date('c', strtotime(sprintf('%s-01 00:00:00', date('Y-m', strtotime('-1 month'))))),
      'timeMax' => date('c', strtotime(sprintf('%04d-%02d-%02d 23:59:59', $endYear, $endMonth, $endDay))),
      'showDeleted' => true,
    ));
  }
}
