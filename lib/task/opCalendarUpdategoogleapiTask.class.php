<?php

class opCalendarUpdategoogleapiTask extends sfBaseTask
{
  protected $consumer = null;

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
      throw new sfException("This task is not allowed. \nPlease allow from 'pc_backend.php/opCalendarPlugin' setting.");
    }

    $crons = opCalendarPluginToolkit::getAllGoogleCalendarCronConfig();
    if (!count($crons))
    {
      $this->logSection('end', 'No cron data');
      exit;
    }

    $this->consumer = new OAuthConsumer(
      opConfig::get('op_calendar_google_data_api_key', 'anonymous'),
      opConfig::get('op_calendar_google_data_api_secret', 'anonymous')
    );
    $this->logSection('prepared', 'start update');

    foreach ($crons as $cron)
    {
      $member = Doctrine_Core::getTable('Member')->find($cron['member_id']);
      if (!$member)
      {
        continue;
      }

      $cron_config = unserialize($cron['serial']);
      foreach ($cron_config['src'] as $src)
      {
        $this->logSection('start', 'update member_id: '.$cron['member_id']."\nscope: ".$src);
        $result = $this->getContents($src, $cron);

        if (!$result)
        {
          continue;
        }

        if (opCalendarPluginToolkit::insertSchedules($result->toArray(), $cron_config['public_flag'], true, $member))
        {
          $this->logSection('end', 'updated success member_id: '.$cron['member_id']."\nscope: ".$src);
        }
        else
        {
          $this->logSection('end', 'updated failed member_id: '.$cron['member_id']."\nscope: ".$src);
        }

        if ($options['interval'])
        {
          $this->logSection('sleep', 'set interval: '.$options['interval'].' second');
          sleep((int)$options['interval']);
        }
      }

    }
  }

  protected function getContents($src, $token)
  {
    // get 3 months data.
    $api = new opCalendarApi(
      $this->consumer,
      new OAuthConsumer($token['token'], $token['secret']),
      opCalendarApiHandler::GET,
      $src,
      array(
        'start-min' => date('Y-m-01\T00:00:00', strtotime('-1 month')),
        'start-max' => sprintf(
          date('Y-m-%02\d\T23:59:59', strtotime('+1 month')),
          opCalendarPluginToolkit::getLastDay(date('m', strtotime('+1 month')))
        ),
        'alt' => 'jsonc',
      )
    );

    $handler = new opCalendarApiHandler($api, new opCalendarApiResultsJsonEvents());
    $results = $handler->execute();

    return $results->is200StatusCode() ? $results : false;
  }
}
