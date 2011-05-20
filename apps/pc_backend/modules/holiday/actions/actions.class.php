<?php

/**
 * holiday actions.
 *
 * @package    OpenPNE
 * @subpackage holiday
 * @author     Shinichi Urabe <urabe@tejimaya.com>
 */
class holidayActions extends sfActions
{
  public function preExecute()
  {
    if (is_callable(array($this->getRoute(), 'getObject')))
    {
      $object = $this->getRoute()->getObject();
      if ($object instanceof Holiday)
      {
        $this->holiday = $object;
      }
    }
  }

 /**
  * Executes list action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeList(sfWebRequest $request)
  {
    $this->newForm = new HolidayForm();
    $holidays = Doctrine::getTable('Holiday')->getHolidayList();
    $this->activeForms = array();
    foreach ($holidays as $holiday)
    {
      $this->activeForms[] = new HolidayForm($holiday);
    }
    $this->form = new BaseForm();
  }

 /**
  * Executes create action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeCreate(sfWebRequest $request)
  {
    $form = new HolidayForm();
    $params = $request->getParameter($form->getName());

    if ($this->processForm($params, $form))
    {
      $this->getUser()->setFlash('notice', 'Holiday has added');
    }
    else
    {
      $this->getUser()->setFlash('error', 'The input has some problem');
    }

    $this->redirect('@holiday');
  }

 /**
  * Executes update action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeUpdate(sfWebRequest $request)
  {
    $form = new HolidayForm($this->holiday);
    $params = $request->getParameter($form->getName());
    $params['id'] = $this->holiday->id;

    if ($this->processForm($params, $form))
    {
      $this->getUser()->setFlash('notice', 'Holiday has fixed');
    }
    else
    {
      $this->getUser()->setFlash('error', 'The input has some problem');
    }

    $this->redirect('@holiday');
  }

 /**
  * Executes delete action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeDelete(sfWebRequest $request)
  {
    $request->checkCSRFProtection();
    $this->holiday->delete();
    $this->getUser()->setFlash('notice', 'Holiday has deleted');

    $this->redirect('@holiday');
  }

  private function processForm($params, BaseForm $form)
  {
    $form->bind($params);
    if ($form->isValid())
    {
      $form->save();

      return true;
    }

    return false;
  }
}
