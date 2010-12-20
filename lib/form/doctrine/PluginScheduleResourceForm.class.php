<?php

/**
 * PluginScheduleResource form.
 *
 * @package    OpenPNE
 * @subpackage form
 * @author     Shinichi Urabe <urabe@tejimaya.com>
 */
abstract class PluginScheduleResourceForm extends BaseScheduleResourceForm
{
  public function setup()
  {
    parent::setup();

    $resourceTypes = $this->getResourceTypes();

    $this->setWidget('name', new sfWidgetFormInputText());
    $this->setWidget('description', new sfWidgetFormInput());
    $this->setWidget('resource_type_id', new sfWidgetFormSelect(array('choices' => $resourceTypes)));
    $this->setWidget('resource_limit', new sfWidgetFormInputText());

    $this->validatorSchema['name'] = new opValidatorString(array('trim' => true));
    $this->validatorSchema['description'] = new opValidatorString(array('rtrim' => true));
    $this->validatorSchema['resource_type_id'] = new sfValidatorChoice(array('choices' => array_keys($resourceTypes)));
    $this->validatorSchema['resource_limit'] = new sfValidatorInteger(array('min' => 1, 'max' => 100));

    $this->useFields(array('name', 'description', 'resource_type_id', 'resource_limit'));
  }

  private function getResourceTypes()
  {
    $params = array(null => '選択してください');
    if (!$resourceTypes = $this->getOption('resource_types'))
    {
      $resourceTypes = Doctrine::getTable('ResourceType')->findAll();
    }

    foreach ($resourceTypes as $resourceType)
    {
      $params[$resourceType->id] = $resourceType->name;
    }

    return $params;
  }
}
