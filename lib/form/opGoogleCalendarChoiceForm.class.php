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
  protected $isNeedIsSaveEmail = true;

  public function configure()
  {
    $list = $this->getOption('list');
    $formList = array();
    foreach ($list as $k => $v)
    {
      $formList[$k] = $v['title'];
      $authorEmail = $v['author']['email'];
    }
    $months = array();
    for ($i = 1; $i <= 12; $i++)
    {
      $months[$i] = sprintf('%d月', $i);
    }
    $this->setWidget('choice', new sfWidgetFormChoice(array(
      'choices'  => $formList,
      'expanded' => true,
    )));
    $this->setWidget('public_flag', new sfWidgetFormChoice(array(
      'choices'  => Doctrine_Core::getTable('Schedule')->getPublicFlags(),
      'expanded' => true,
    )));
    $this->setWidget('months', new sfWidgetFormSelect(array(
      'choices'  => $months,
    )));
    $save_email_check = array(1 => 'Googleカレンダーで使っているemailをSNSに保存する');
    $this->setWidget('is_save_email', new sfWidgetFormChoice(array(
      'choices'  => $save_email_check,
      'multiple' => true,
      'expanded' => true,
    )));
    $this->setDefault('months', date('n'));
    $this->validatorSchema['choice'] = new sfValidatorChoice(array(
      'choices' => array_keys($formList),
    ));
    $this->setDefault('public_flag', ScheduleTable::PUBLIC_FLAG_SCHEDULE_MEMBER);
    $this->validatorSchema['public_flag'] = new sfValidatorChoice(array(
      'choices' => array_keys(Doctrine_Core::getTable('Schedule')->getPublicFlags()),
    ));
    $this->validatorSchema['is_save_email'] = new sfValidatorChoice(array(
      'choices' => array_keys($save_email_check),
      'multiple' => true,
      'required' => false,
    ));
    $this->validatorSchema['months'] = new sfValidatorChoice(array(
      'choices' => array_keys($months),
    ));
    $this->widgetSchema->setLabel('choice', 'Google Calendars');
    $this->widgetSchema->setLabel('months', '読み込む月');
    $this->widgetSchema->setLabel('is_save_email', 'Google Calendar のemailの保存');
    $this->widgetSchema->setNameFormat('google_calendars[%s]');

    if (opCalendarPluginToolkit::seekEmailAndGetMemberId($authorEmail))
    {
      unset($this['is_save_email']);
      $this->isNeedIsSaveEmail = false;
    }
  }

  public function isNeedIsSaveEmail()
  {
    return $this->isNeedIsSaveEmail;
  }

  public function save()
  {
    $values = $this->getValues();
    $list = $this->getOption('list');
    $opGoogleCalendarOAuth = $this->getOption('opGoogleCalendarOAuth');
    $entry = $list[$values['choice']];

    $result = $opGoogleCalendarOAuth->getContents(
      str_replace(opGoogleCalendarOAuth::SCOPE, '', $entry['contents']['src']),
      'opCalendarApiResultsJsonEvents',
      opCalendarApiHandler::GET,
      array(
        'start-min' => sprintf('%04d-%02d-01T00:00:00', date('Y'), $values['months']),
        'start-max' => sprintf('%04d-%02d-%02dT23:59:59', date('Y'), $values['months'], $this->getLastDay($values['months'])),
        'alt' => 'jsonc',
      )
    );

    return opCalendarPluginToolkit::insertSchedules($result->toArray(), $values['public_flag'], isset($values['is_save_email'][0]));
  }

  private function getLastDay($month)
  {
    $limitedMonths = array(
      2 => $this->isLeap((int)date('Y')) ? 28 : 29,
      4 => 30,
      6 => 30,
      9 => 30,
      11 => 30,
    );
    if (isset($limitedMonths[$month]))
    {
      return $limitedMonths[$month];
    }

    return 31;
  }

  private function isLeap($year)
  {
    if (0 == $year % 4)
    {
      if (0 == $year % 100)
      {
        if (0 == $year % 400)
        {
          return true;
        }

        return false;
      }

      return true;
    }

    return false;
  }
}
