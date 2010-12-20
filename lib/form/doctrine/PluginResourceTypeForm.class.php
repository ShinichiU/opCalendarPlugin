<?php

/**
 * PluginResourceType form.
 *
 * @package    OpenPNE
 * @subpackage form
 * @author     Shinichi Urabe <urabe@tejimaya.com>
 */
abstract class PluginResourceTypeForm extends BaseResourceTypeForm
{
  public function setup()
  {
    parent::setup();

    $this->setWidget('name', new sfWidgetFormInputText());
    $this->setWidget('description', new sfWidgetFormInput());

    $this->validatorSchema['name'] = new opValidatorString(array('trim' => true));
    $this->validatorSchema['description'] = new opValidatorString(array('rtrim' => true));

    $this->useFields(array('name', 'description'));
  }
}
