<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * MemberConfigScheduleForm.
 *
 * @package    opCalendarPlugin
 * @subpackage form
 * @author     Shinichi Urabe <urabe@tejimaya.com>
 */
class MemberConfigScheduleForm extends MemberConfigForm
{
  const PUBLIC_FLAG = 'schedule_public_flag';
  const FIRST_DAY_OF_THE_WEEK = 'first_day_of_the_week';

  protected
    $category = 'schedule',
    $firstDayOfTheWeeks = array(
      0 => 'Mon',
      1 => 'Tue',
      2 => 'Wed',
      3 => 'Thu',
      4 => 'Fri',
      5 => 'Sat',
      6 => 'Sun',
  );

  public function configure()
  {
    $this->widgetSchema[self::PUBLIC_FLAG] = new sfWidgetFormChoice(array(
      'choices'  => Doctrine::getTable('Schedule')->getPublicFlags(),
      'expanded' => true,
      'default'  => $this->getConfig(self::PUBLIC_FLAG, ScheduleTable::PUBLIC_FLAG_SNS),
      'label'    => 'Public flag',
    ));
    $this->widgetSchema[self::FIRST_DAY_OF_THE_WEEK] = new sfWidgetFormChoice(array(
      'choices'  => $this->firstDayOfTheWeeks,
      'default'  => $this->getConfig(self::FIRST_DAY_OF_THE_WEEK, 0),
      'label'    => 'First day of the week',
    ));
    $this->widgetSchema->setHelp(self::PUBLIC_FLAG, 'Default public flag for your new schedules. Past schedules are not changed.');

    $this->validatorSchema[self::PUBLIC_FLAG] = new sfValidatorChoice(array(
      'choices' => array_keys(Doctrine::getTable('Schedule')->getPublicFlags()),
    ));
    $this->validatorSchema[self::FIRST_DAY_OF_THE_WEEK] = new sfValidatorChoice(array(
      'choices' => array_keys($this->firstDayOfTheWeeks),
    ));
  }

  private function getConfig($name, $default = null)
  {
    $value = $this->member->getConfig($name, $default);

    return null === $value ? $default : $value;
  }
}
