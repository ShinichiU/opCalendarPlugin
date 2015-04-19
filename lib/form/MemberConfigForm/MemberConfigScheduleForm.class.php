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
  const IS_GOOGLE_CALENDAR_OAUTH_KEY_REVOKE = 'is_Google_calendar_OAuth_key_revoke';

  protected
    $category = 'schedule';

  protected
    $opCalendarOAuth;

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

    if ($this->isOAuthAuthenticate())
    {
      $check = array(1 => 'Is delete.');
      $this->setWidget(self::IS_GOOGLE_CALENDAR_OAUTH_KEY_REVOKE, new sfWidgetFormChoice(array(
        'choices'  => $check,
        'multiple' => true,
        'expanded' => true,
      )));
      $this->setValidator(self::IS_GOOGLE_CALENDAR_OAUTH_KEY_REVOKE, new sfValidatorChoice(array(
        'choices' => array_keys($check),
        'multiple' => true,
        'required' => false,
      )));
    }
  }

  public function save()
  {
    if ($this->isOAuthAuthenticate() && array_key_exists(self::IS_GOOGLE_CALENDAR_OAUTH_KEY_REVOKE, $this->values))
    {
      $isDelete = (bool) $this->values[self::IS_GOOGLE_CALENDAR_OAUTH_KEY_REVOKE];
      unset($this->values[self::IS_GOOGLE_CALENDAR_OAUTH_KEY_REVOKE]);

      if ($isDelete)
      {
        $this->opCalendarOAuth->getClient()->revokeToken();

        Doctrine_Core::getTable('MemberConfig')->createQuery()
          ->delete()
          ->whereIn('name', array(
            'google_calendar_oauth_access_token',
            'google_cron_update',
            'google_cron_update_params',
          ))
          ->execute();
      }
    }

    return parent::save();
  }

  private function getConfig($name, $default = null)
  {
    $value = $this->member->getConfig($name, $default);

    return null === $value ? $default : $value;
  }

  private function isOAuthAuthenticate()
  {
    $this->opCalendarOAuth = opCalendarOAuth::getInstance();

    return $this->opCalendarOAuth->authenticate($this->member);
  }
}
