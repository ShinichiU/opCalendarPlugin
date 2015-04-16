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

  protected $fileModel = null;

  const FILE_NAME = 'op_calendar_google_data_api_json_file';

  public function getKeys()
  {
    return $this->keys;
  }

  public function configure()
  {
    $this->fileModel = new opGoogleOAuthJson;
    $fileKey = opGoogleOAuthJson::FILE_NAME;

    $options = array(
      'file_src'     => '',
      'is_image'     => false,
      'with_delete'  => true,
      'delete_label' => 'ファイルを削除する',
      'label'        => false,
      'edit_mode'    => false,
    );

    if ($this->fileModel->hasFile())
    {
      sfContext::getInstance()->getConfiguration()->loadHelpers('Partial');
      $options['template'] = get_partial('opCalendarPlugin/formEditFile', array('file' => $this->fileModel->getFile()));
      $options['edit_mode'] = true;
      $this->setValidator($fileKey.'_delete', new sfValidatorBoolean(array('required' => false)));
    }

    $this->setWidget($fileKey, new sfWidgetFormInputFileEditable($options));
    $this->setValidator($fileKey, new opValidatorGoogleApiJsonFile(array('required' => false)));

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
    $fileKey = opGoogleOAuthJson::FILE_NAME;

    foreach ($this->getValues() as $key => $value)
    {
      if (isset($this->keys[$key]))
      {
        Doctrine_Core::getTable('SnsConfig')->set($key, (bool) $value);
      }
      elseif ($key === $fileKey.'_delete' && (bool) $value && $file = $this->fileModel->getFile())
      {
        $this->fileModel->delete();
      }
      elseif ($key === $fileKey && $value instanceof sfValidatedFile)
      {
        $this->fileModel->saveFile($value);
      }
    }
  }
}
