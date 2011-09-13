<?php

/**
 * Google data api consumer key Form
 *
 * @package    OpenPNE
 * @subpackage form
 * @author     Shinichi Urabe <urabe@tejimaya.com>
 */
class opGoogleDataApiConsumerKeyForm extends BaseForm
{
  protected $keys = array(
    'op_calendar_google_data_api_key' => 'Google Data API consumer key',
    'op_calendar_google_data_api_secret' => 'Google Data API consumer secret',
    'op_calendar_google_data_api_is_active' => 'Google Data API を使用しますか?',
    'op_calendar_google_data_api_auto_update' => 'Google Data API 自動更新機能使用しますか?(cronの設置が必要)',
  );

  public function getKeys()
  {
    return $this->keys;
  }

  public function configure()
  {
    foreach ($this->keys as $k => $v)
    {
      if ('op_calendar_google_data_api_is_active' === $k || 'op_calendar_google_data_api_auto_update' === $k)
      {
        $check = array(1 => 'yes');
        $this->setWidget($k, new sfWidgetFormChoice(array(
          'choices'  => $check,
          'multiple' => true,
          'expanded' => true,
        )));
        $this->setValidator($k, new sfValidatorChoice(array(
          'choices' => array_keys($check),
          'multiple' => true,
          'required' => false,
        )));
        $this->setDefault($k, opConfig::get($k) ? 1 : 0);
      }
      else
      {
        $this->setWidget($k, new sfWidgetFormInput());
        $this->setValidator($k, new opValidatorString(array('required' => false, 'trim' => true)));
        $this->setDefault($k, opConfig::get($k));
      }
      $this->widgetSchema->setLabel($k, $v);
    }
    $this->widgetSchema->setNameFormat('google_data_api[%s]');
  }

  public function save()
  {
    foreach ($this->getValues() as $k => $v)
    {
      if ('op_calendar_google_data_api_is_active' === $k || 'op_calendar_google_data_api_auto_update' === $k)
      {
        $v = (bool)$v;
      }
      Doctrine_Core::getTable('SnsConfig')->set($k, $v);
    }
  }
}
