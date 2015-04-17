<?php

/**
 * calendarApi actions.
 *
 * @package    OpenPNE
 * @subpackage calendarApi
 * @author     Shinichi Urabe <urabe@tejimaya.com>
 */
class calendarApiActions extends sfActions
{
  public function preExecute()
  {
    $this->forward404Unless(opConfig::get('op_calendar_google_data_api_is_active', false));
    $this->opGoogleCalendarOAuth = opGoogleCalendarOAuth::getInstance();
  }

 /**
  * Executes index action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->forward404If($this->opGoogleCalendarOAuth->authenticate());

    $this->redirect($this->opGoogleCalendarOAuth->getClient()->createAuthUrl());
  }

  const TOKEN_SESSEION_KEY = 'calendar_api_access_token';

 /**
  * Executes callback action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeCallback(sfWebRequest $request)
  {
    $code = $request['code'];

    $client = $this->opGoogleCalendarOAuth->getClient();
    $client->authenticate($code);

    $this->getUser()->setFlash(self::TOKEN_SESSEION_KEY, $client->getAccessToken());

    $this->redirect('@calendar_api_set_access_token');
  }

 /**
  * Executes setAcessToken action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeSetAccessToken(sfWebRequest $request)
  {
    $tokens = $this->getUser()->getFlash(self::TOKEN_SESSEION_KEY);
    $this->forward404Unless($tokens);

    $member = $this->getUser()->getMember();
    $this->opGoogleCalendarOAuth->saveAccessToken($member, $tokens);

    $this->getUser()->setFlash('notice', 'Google Calendar API is now available.');
    $this->redirect('@calendar_api_import');
  }

 /**
  * Executes index action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeImport(sfWebRequest $request)
  {
    $this->forwardUnless($this->opGoogleCalendarOAuth->authenticate(), 'calendarApi', 'index');

    $calendar = new Google_Service_Calendar($this->opGoogleCalendarOAuth->getClient());
    $list = $calendar->calendarList->listCalendarList();

    if (!$list)
    {
      $this->getUser()->setFlash('error', 'カレンダーの読み込みに失敗しました');

      $this->redirect('@calendar');
    }

    $this->form = new opGoogleCalendarChoiceForm(null, array(
      'list' => $list['items'],
      'googleCalendar' => $calendar,
    ));

    if ($request->isMethod(sfWebRequest::POST))
    {
      $this->form->bind($request->getParameter($this->form->getName()));
      if ($this->form->isValid())
      {
        $r = $this->form->save();

        $params = $request->getParameter($this->form->getName());
        $this->getUser()->setFlash('notice', $r ? 'Success to fetch the Calendar.' : 'Fail to fetch some part of the Calendar.');
        $this->redirect(sprintf('@calendar_year_month?year=%04d&month=%02d', date('Y'), $params['months']));
      }
    }
  }
}
