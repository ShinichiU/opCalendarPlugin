<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opCalendarPlugin actions.
 *
 * @package    OpenPNE
 * @subpackage opCalendarPlugin
 * @author     Shinichi Urabe
 */
class opCalendarPluginActions extends sfActions
{
  const SCHEDULE_RESOURCE_CREATE = 'schedule_resource_create';
  const SCHEDULE_RESOURCE_UPDATE = 'schedule_resource_update';
  const SCHEDULE_RESOURCE_DELETE = 'schedule_resource_delete';
  const RESOURCE_TYPE_CREATE = 'resource_type_create';
  const RESOURCE_TYPE_UPDATE = 'resource_type_update';
  const RESOURCE_TYPE_DELETE = 'resource_type_delete';
  const GOOGLE_DATA_API_UPDATE = 'google_data_api_update';

  private $messages = array(
    self::SCHEDULE_RESOURCE_CREATE => array(
      'error' => 'スケジュールリソースの作成に失敗しました',
      'notice' => 'スケジュールリソースを作成しました',
    ),
    self::SCHEDULE_RESOURCE_UPDATE => array(
      'error' => 'スケジュールリソースの更新に失敗しました',
      'notice' => 'スケジュールリソースを更新しました',
    ),
    self::SCHEDULE_RESOURCE_DELETE => array(
      'error' => 'スケジュールリソースの削除に失敗しました',
      'notice' => 'スケジュールリソースを削除しました',
    ),
    self::RESOURCE_TYPE_CREATE => array(
      'error' => 'リソースタイプの作成に失敗しました',
      'notice' => 'リソースタイプを作成しました',
    ),
    self::RESOURCE_TYPE_UPDATE => array(
      'error' => 'リソースタイプの更新に失敗しました',
      'notice' => 'リソースタイプを更新しました',
    ),
    self::RESOURCE_TYPE_DELETE => array(
      'error' => 'リソースタイプの削除に失敗しました',
      'notice' => 'リソースタイプを削除しました',
    ),
    self::GOOGLE_DATA_API_UPDATE => array(
      'error' => 'Google DATA API CONSUMER の設定に失敗しました',
      'notice' => 'Google DATA API CONSUMER を設定しました',
    ),
  );

  public function preExecute()
  {
    if (is_callable(array($this->getRoute(), 'getObject')))
    {
      $object = $this->getRoute()->getObject();
      if ($object instanceof ScheduleResource)
      {
        $this->scheduleResource = $object;
      }
      elseif ($object instanceof ResourceType)
      {
        $this->resourceType = $object;
      }
    }
  }

 /**
  * Executes index action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $resourceTypes = Doctrine::getTable('ResourceType')->findAll();
    $scheduleResources = Doctrine::getTable('ScheduleResource')->findAll();
    $this->newScheduleResourceForm = new ScheduleResourceForm(null, array('resource_types' => $resourceTypes));
    $this->scheduleResourceForms = array();
    foreach ($scheduleResources as $scheduleResource)
    {
      $this->scheduleResourceForms[] = new ScheduleResourceForm($scheduleResource, array('resource_types' => $resourceTypes));
    }

    $this->newResourceTypeForm = new ResourceTypeForm();
    $this->resourceTypeForms = array();
    foreach ($resourceTypes as $resourceType)
    {
      $this->resourceTypeForms[] = new ResourceTypeForm($resourceType);
    }

    $this->googleApiForm = new opGoogleDataApiConsumerKeyForm();
  }

 /**
  * Executes resourceCreate action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeResourceCreate(sfWebRequest $request)
  {
    $this->processForm(new ScheduleResourceForm(), $request, self::SCHEDULE_RESOURCE_CREATE);
  }

 /**
  * Executes resourceUpdate action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeResourceUpdate(sfWebRequest $request)
  {
    $this->processForm(new ScheduleResourceForm($this->scheduleResource), $request, self::SCHEDULE_RESOURCE_UPDATE);
  }

 /**
  * Executes resourceDeleteConfirm action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeResourceDeleteConfirm(sfWebRequest $request)
  {
    $this->form = new BaseForm();
  }

 /**
  * Executes resourceDelete action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeResourceDelete(sfWebRequest $request)
  {
    $this->processDelete($this->scheduleResource, $request, self::SCHEDULE_RESOURCE_DELETE);
  }

 /**
  * Executes resourceTypeCreate action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeResourceTypeCreate(sfWebRequest $request)
  {
    $this->processForm(new ResourceTypeForm(), $request, self::RESOURCE_TYPE_CREATE);
  }

 /**
  * Executes resourceTypeUpdate action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeResourceTypeUpdate(sfWebRequest $request)
  {
    $this->processForm(new ResourceTypeForm($this->resourceType), $request, self::RESOURCE_TYPE_UPDATE);
  }

 /**
  * Executes resourceTypeDeleteConfirm action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeResourceTypeDeleteConfirm(sfWebRequest $request)
  {
    $this->form = new BaseForm();
  }

 /**
  * Executes resourceTypeDelete action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeResourceTypeDelete(sfWebRequest $request)
  {
    $this->processDelete($this->resourceType, $request, self::RESOURCE_TYPE_DELETE);
  }

 /**
  * Executes googleDataAPIUpdate action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeGoogleDataAPIUpdate(sfWebRequest $request)
  {
    $this->processForm(new opGoogleDataApiConsumerKeyForm(), $request, self::GOOGLE_DATA_API_UPDATE);
  }

  private function processForm(BaseForm $form, sfWebRequest $request, $type = null)
  {
    $name = $form->getName();
    $form->bind($request->getParameter($name), $request->getFiles($name));
    if ($this->setFlashMessageByType($form->isValid(), $type))
    {
      $form->save();
    }

    $this->redirect('@opCalendarPlugin');
  }

  private function processDelete(Doctrine_Record $obj, sfWebRequest $request, $type = null)
  {
    $request->checkCSRFProtection();
    $this->setFlashMessageByType($obj->delete(), $type);

    $this->redirect('@opCalendarPlugin');
  }

  private function setFlashMessageByType($condition, $type = null)
  {
    $flash = $condition ? 'notice' : 'error';
    if (null !== $type && isset($this->messages[$type][$flash]))
    {
      $this->getUser()->setFlash($flash, $this->messages[$type][$flash]);
    }

    return $condition;
  }
}
