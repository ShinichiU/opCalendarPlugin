<?php

class scheduleActions extends sfActions
{
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

    return sfView::SUCCESS;
  }

  public function executeCreate(sfWebRequest $request)
  {
    $this->form = new ScheduleForm();
    $this->form->getObject()->setMemberId($this->getUser()->getMemberId());
    $this->processForm($request, $this->form);

    $this->setTemplate('new');

    return sfView::SUCCESS;
  }

  public function executeShow(sfWebRequest $request)
  {
    $this->schedule = $this->getRoute()->getObject();
    $this->forward404Unless($this->schedule->isEditable($this->getUser()->getMemberId()));

    return sfView::SUCCESS;
  }

  public function executeEdit(sfWebRequest $request)
  {
    $this->schedule = $this->getRoute()->getObject();
    $this->forward404Unless($this->schedule->isEditable($this->getUser()->getMemberId()));
    $this->form = new ScheduleForm($this->schedule);

    return sfView::SUCCESS;
  }

  public function executeUpdate(sfWebRequest $request)
  {
    $this->schedule = $this->getRoute()->getObject();
    $this->forward404Unless($this->schedule->isEditable($this->getUser()->getMemberId()));
    $this->form = new ScheduleForm($this->schedule);
    $this->processForm($request, $this->form);
    $this->setTemplate('edit');

    return sfView::SUCCESS;
  }

  public function executeDeleteConfirm(sfWebRequest $request)
  {
    $this->schedule = $this->getRoute()->getObject();
    $this->forward404Unless($this->schedule->isEditable($this->getUser()->getMemberId()));
    $this->form = new sfForm();

    return sfView::SUCCESS;
  }

  public function executeDelete(sfWebRequest $request)
  {
    $request->checkCSRFProtection();
    $this->schedule = $this->getRoute()->getObject();
    $this->forward404Unless($this->schedule->isEditable($this->getUser()->getMemberId()));
    $this->schedule->delete();
    $this->getUser()->setFlash('notice', '予定を削除しました。');
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
      $this->getUser()->setFlash('notice', '予定を追加しました');
    }
    else
    {
      $this->getUser()->setFlash('error', '入力した値に問題があります');
    }

    $this->redirect('@homepage'.$paramstring);
  }

  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $form->bind(
      $request->getParameter($form->getName()),
      $request->getFiles($form->getName())
    );

    if ($form->isValid())
    {
      $schedule = $form->save();
      $this->redirect('@calendar');
    }
  }
}
