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

  protected
    $category = 'schedule';

  public function configure()
  {
    $this->widgetSchema[self::PUBLIC_FLAG] = new sfWidgetFormChoice(array(
      'choices'  => Doctrine::getTable('Schedule')->getPublicFlags(),
      'expanded' => true,
      'default'  => $this->getConfig(self::PUBLIC_FLAG, ScheduleTable::PUBLIC_FLAG_SNS),
      'label'    => 'Public flag',
    ));
    $this->widgetSchema->setHelp(self::PUBLIC_FLAG, 'Default public flag for your new schedules. Past schedules are not changed.');

    $this->validatorSchema[self::PUBLIC_FLAG] = new sfValidatorChoice(array(
      'choices' => array_keys(Doctrine::getTable('Schedule')->getPublicFlags()),
    ));
  }

  private function getConfig($name, $default = null)
  {
    $value = $this->member->getConfig($name, $default);

    return null === $value ? $default : $value;
  }
}
