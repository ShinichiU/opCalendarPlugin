<?php

class scheduleActions extends sfActions
{
  public function preExecute()
  {
    if (is_callable(array($this->getRoute(), 'getObject')))
    {
      $object = $this->getRoute()->getObject();
      if ($object instanceof Schedule)
      {
        $this->schedule = $object;
        $this->member = $this->schedule->Member;
      }
    }
  }

  public function executeNew(sfWebRequest $request)
  {
    $this->form = new ScheduleForm();
    $date = sprintf('%02d-%02d-%02d',
      (int)$request->getParameter('year', date('Y')),
      (int)$request->getParameter('month', date('m')),
      (int)$request->getParameter('day', date('d'))
    );
    $this->form->setDefault('start_date', $date);
    $this->form->setDefault('end_date', $date);
  }

  public function executeCreate(sfWebRequest $request)
  {
    $this->form = new ScheduleForm();
    $this->form->getObject()->setMemberId($this->getUser()->getMemberId());
    $this->processForm($request, $this->form);

    $this->setTemplate('new');
  }

  public function executeShow(sfWebRequest $request)
  {
    $this->forward404Unless($this->schedule->isShowable($this->getUser()->getMemberId()));
  }

  public function executeEdit(sfWebRequest $request)
  {
    $this->forward404Unless($this->schedule->isEditable($this->getUser()->getMemberId()));
    $this->form = new ScheduleForm($this->schedule);
  }

  public function executeUpdate(sfWebRequest $request)
  {
    $this->forward404Unless($this->schedule->isEditable($this->getUser()->getMemberId()));
    $this->form = new ScheduleForm($this->schedule);
    $this->processForm($request, $this->form);
    $this->setTemplate('edit');
  }

  public function executeDeleteConfirm(sfWebRequest $request)
  {
    $this->forward404Unless($this->schedule->isEditable($this->getUser()->getMemberId()));
    $this->form = new BaseForm();
  }

  public function executeDelete(sfWebRequest $request)
  {
    $request->checkCSRFProtection();
    $this->schedule = $this->getRoute()->getObject();
    $this->forward404Unless($this->schedule->isEditable($this->getUser()->getMemberId()));
    $this->schedule->delete();
    $this->getUser()->setFlash('notice', 'The schedule has deleted');
    $this->redirect('@calendar');
  }

 /**
  * Executes mini create action
  * マイホームからの予定の追加
  *
  * @param sfWebRequest $request A request object
  */
  public function executeMiniCreate(sfWebRequest $request)
  {
    $form = new MiniScheduleForm();
    $params = $request->getParameter($form->getName());
    $w = $request->getParameter('calendar_weekparam', 0);
    $paramstring = $w ? '?calendar_weekparam='.$w : '';

    $form->bind($request->getParameter($form->getName()));

    if ($form->isValid())
    {
      $form->save();
      $this->getUser()->setFlash('notice', 'The schedule has added');
    }
    else
    {
      $this->getUser()->setFlash('error', 'The input has some problem');
    }

    $this->redirect('@homepage'.$paramstring);
  }

  protected function processForm(sfWebRequest $request, BaseForm $form)
  {
    $form->bind(
      $request->getParameter($form->getName()),
      $request->getFiles($form->getName())
    );

    if ($form->isValid())
    {
      $schedule = $form->save();
      $this->getUser()->setFlash('notice', $form->isNew() ? 'The schedule has added' : 'The schedule has updated');
      $results = explode('-', $schedule->start_date);
      $this->redirect(sprintf('@calendar_year_month?year=%d&month=%d', $results[0], $results[1]));
    }
  }
}
