<?php

/**
 * PluginHoliday form.
 *
 * @package    opCalendarPlugin
 * @subpackage form
 * @author     Shinichi Urabe <urabe@tejimaya.com>
 */
abstract class PluginHolidayForm extends BaseHolidayForm
{
  protected
    $years = array(),
    $months = array(),
    $days = array();

  public function setup()
  {
    parent::setup();

    $this->generateMonthDate();

    $this->setWidget('name', new sfWidgetFormInputText());
    $this->setWidget('year', new sfWidgetFormInputText());
    $this->setWidget('month', new sfWidgetFormSelect(array('choices' => $this->months)));
    $this->setWidget('day', new sfWidgetFormSelect(array('choices' => $this->days)));

    $this->validatorSchema['name'] = new opValidatorString(array('trim' => true));
    $this->validatorSchema['year'] = new sfValidatorInteger(array('required' => false, 'min' => 1));
    $this->validatorSchema['month'] = new sfValidatorChoice(array('choices' => array_keys($this->months)));
    $this->validatorSchema['day'] = new sfValidatorChoice(array('choices' => array_keys($this->days)));

    $this->validatorSchema->setPostValidator(new sfValidatorCallback(
      array('callback' => array($this, 'validateMonthDay'))
    ));
    $this->useFields(array('name', 'year', 'month', 'day'));
  }

  private function generateMonthDate()
  {
    for ($i = 1; $i <= 12; $i++)
    {
      $this->months[$i] = sprintf('%s月', $i);
    }
    for ($i = 1; $i <= 31; $i++)
    {
      $this->days[$i] = sprintf('%s日', $i);
    }
  }

  public function validateMonthDay(sfValidatorBase $validator, $values)
  {
    $limitedMonths = array(
      2 => $values['year'] ? opCalendarPluginToolkit::isLeap((int)$values['year']) ? 29 : 28 : 29,
      4 => 30,
      6 => 30,
      9 => 30,
      11 => 30,
    );
    if (isset($limitedMonths[$values['month']]) && $limitedMonths[(int)$values['month']] < (int)$values['day'])
    {
      throw new sfValidatorError($validator, 'invalid');
    }

    return $values;
  }
}
