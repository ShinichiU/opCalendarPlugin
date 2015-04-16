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
    'op_calendar_google_data_api_is_active' => 'Google Data API を使用しますか?',
    'op_calendar_google_data_api_auto_update' => 'Google Data API 自動更新機能使用しますか?(cronの設置が必要)',
  );

  public function getKeys()
  {
    return $this->keys;
  }

  public function configure()
  {
    $check = array(1 => 'yes');
    foreach ($this->keys as $key => $value)
    {
      $this->setWidget($key, new sfWidgetFormChoice(array(
        'choices'  => $check,
        'multiple' => true,
        'expanded' => true,
      )));
      $this->setValidator($key, new sfValidatorChoice(array(
        'choices' => array_keys($check),
        'multiple' => true,
        'required' => false,
      )));
      $this->setDefault($key, (int) opConfig::get($key));
      $this->widgetSchema->setLabel($key, $value);
    }

    $this->widgetSchema->setNameFormat('google_data_api[%s]');
  }

  public function save()
  {
    foreach ($this->getValues() as $key => $value)
    {
      if (isset($this->keys[$key]))
      {
        Doctrine_Core::getTable('SnsConfig')->set($key, (bool) $value);
      }
    }
  }
}
