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
  );

  public function getKeys()
  {
    return $this->keys;
  }

  public function configure()
  {
    foreach ($this->keys as $k => $v)
    {
      $this->setWidget($k, new sfWidgetFormInput());
      $this->setValidator($k, new opValidatorString(array('required' => false, 'trim' => true)));
      $this->setDefault($k, opConfig::get($k));
      $this->widgetSchema->setLabel($k, $v);
    }
    $this->widgetSchema->setNameFormat('google_data_api[%s]');
  }

  public function save()
  {
    foreach ($this->getValues() as $k => $v)
    {
      Doctrine_Core::getTable('SnsConfig')->set($k, $v);
    }
  }
}
