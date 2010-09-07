<?php

/**
 * PluginSchedule form.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: sfDoctrineFormPluginTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
abstract class PluginScheduleForm extends BaseScheduleForm
{
  protected
    $dateTime = array();

  public function setup()
  {
    parent::setup();

    $this->generateDateTime();

    $this->setWidget('title', new sfWidgetFormInput());

    $dateObj = new sfWidgetFormI18nDate(array(
      'format' => '%year%年%month%月%day%日',
      'culture' => 'ja_JP',
      'month_format' => 'number',
      'years' => $this->dateTime['years'],
    ));
    $this->setWidget('start_date', $dateObj);
    $this->setWidget('end_date', $dateObj);

    $timeObj = new sfWidgetFormTime(array(
      'with_seconds' => true,
      'format' => '%hour%時%minute%分',
      'minutes' => $this->dateTime['minutes'],
    ));
    $this->setWidget('start_time', $timeObj);
    $this->setWidget('end_time', $timeObj);

    $this->validatorSchema['title'] = new opValidatorString(array('trim' => true));
    $this->validatorSchema->setPostValidator(new sfValidatorCallback(
      array('callback' => array($this, 'validateEndDate')),
      array('invalid' => '終了日時は開始日時より前に設定できません')
    ));

    $this->useFields(array('title', 'start_date', 'start_time', 'end_date', 'end_time', 'body'));
  }

  private function generateDateTime()
  {
    $startYear = (int)date('Y');
    $endYear = $startYear + 1;
    $years = range($startYear, $endYear);
    $this->dateTime['years'] = array_combine($years, $years);

    $minutes = array(0, 15, 30, 45);
    $this->dateTime['minutes'] = array_combine($minutes, $minutes);
  }

  public function validateEndDate(sfValidatorBase $validator, $values)
  {
    $start_datetime = $values['start_date'];
    $end_datetime   = $values['end_date'];
    if (isset($values['start_time']) && isset($values['end_time']))
    {
      $start_datetime .= ' '.$values['start_time'];
      $end_datetime   .= ' '.$values['end_time'];
    }
    $start = strtotime($start_datetime);
    $end   = strtotime($end_datetime);

    if ($start > $end)
    {
      throw new sfValidatorError($validator, 'invalid');
    }

    return $values;
  }
}
