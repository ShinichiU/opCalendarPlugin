<?php

/**
 * Google data api consumer key Form
 *
 * @package    OpenPNE
 * @subpackage form
 * @author     Shinichi Urabe <urabe@tejimaya.com>
 */
class opGoogleCalendarChoiceForm extends BaseForm
{
  public function configure()
  {
    $months = array();
    for ($i = 1; $i <= 12; $i++)
    {
      $months[$i] = sprintf('%02d', $i);
    }
    $this->setWidget('public_flag', new sfWidgetFormChoice(array(
      'choices'  => Doctrine_Core::getTable('Schedule')->getPublicFlags(),
      'expanded' => true,
    )));
    $this->setWidget('months', new sfWidgetFormSelect(array(
      'choices'  => $months,
    )));
    $save_email_check = array(1 => 'Save the email on Google Calendar to SNS');
    $this->setDefault('months', date('n'));
    $this->validatorSchema['public_flag'] = new sfValidatorChoice(array(
      'choices' => array_keys(Doctrine_Core::getTable('Schedule')->getPublicFlags()),
    ));
    $this->member = sfContext::getInstance()->getUser()->getMember();
    $this->setDefault('public_flag', $this->member->getConfig('schedule_public_flag', ScheduleTable::PUBLIC_FLAG_SCHEDULE_MEMBER));
    $this->validatorSchema['months'] = new sfValidatorChoice(array(
      'choices' => array_keys($months),
    ));
    $this->widgetSchema->setLabel('months', 'Month to be fetched');

    if (Doctrine_Core::getTable('SnsConfig')->get('op_calendar_google_data_api_auto_update', false))
    {
      $check = array(1 => 'Do Auto Update');
      $this->setWidget('google_cron_update', new sfWidgetFormChoice(array(
        'choices'  => $check,
        'multiple' => true,
        'expanded' => true,
      )));
      $this->setValidator('google_cron_update', new sfValidatorChoice(array(
        'choices' => array_keys($check),
        'multiple' => true,
        'required' => false,
      )));
      $this->setDefault('google_cron_update', $this->member->getConfig('google_cron_update', 0));
      $this->widgetSchema->setLabel('google_cron_update', 'Google Calendar Auto Update');
    }

    $this->mergePostValidator(new sfValidatorCallback(array('callback' => array($this, 'validateId'))));

    $this->widgetSchema->setNameFormat('google_calendars[%s]');
  }

  public function validateId($validator, $values, $arguments = array())
  {
    if (opCalendarOAuth::getInstance()->isAlreadyUsedCalendarId($this->member))
    {
      throw new sfValidatorError($validator, 'This calendar is used other SNS Member.');
    }

    return $values;
  }

  public function save()
  {
    $values = $this->getValues();
    $id = $this->getOption('id');
    $googleCronUpdate = isset($values['google_cron_update']) && (bool) $values['google_cron_update'];
    $publicFlag = $values['public_flag'];
    $calendar = $this->getOption('googleCalendar');

    $lastDay = opCalendarPluginToolkit::getLastDay($values['months']);
    $yearMonth = sprintf('%04d-%02d', date('Y'), $values['months']);

    $events = $calendar->events->listEvents($id, array(
      'timeMin' => date('c', strtotime(sprintf('%s-01 00:00:00', $yearMonth))),
      'timeMax' => date('c', strtotime(sprintf('%s-%02d 23:59:59', $yearMonth, $lastDay))),
      'showDeleted' => true,
    ));

    if (!$events)
    {
      return false;
    }

    opCalendarPluginToolkit::updateGoogleCalendarCronFlags($googleCronUpdate, $publicFlag, $this->member);

    return opCalendarPluginToolkit::insertSchedules($events, $publicFlag);
  }
}
