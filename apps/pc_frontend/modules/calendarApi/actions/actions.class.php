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
    $this->opCalendarOAuth = opCalendarOAuth::getInstance();
  }

 /**
  * Executes index action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeIndex(sfWebRequest $request)
  {
    $this->forward404If($this->opCalendarOAuth->authenticate());

    $this->redirect($this->opCalendarOAuth->getClient()->createAuthUrl());
  }

  const TOKEN_CODE_KEY = 'calendar_api_access_token_code';

 /**
  * Executes callback action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeCallback(sfWebRequest $request)
  {
    $this->getUser()->setFlash(self::TOKEN_CODE_KEY, $request['code']);

    $this->redirect('@calendar_api_set_access_token');
  }

 /**
  * Executes setAcessToken action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeSetAccessToken(sfWebRequest $request)
  {
    $code = $this->getUser()->getFlash(self::TOKEN_CODE_KEY);
    $this->forward404Unless($code);

    $client = $this->opCalendarOAuth->getClient();
    $client->authenticate($code);

    $member = $this->getUser()->getMember();
    $token = $client->getAccessToken();
    if ($this->opCalendarOAuth->isAlreadyUsedCalendarId($member, $token))
    {
      $member = $this->getUser()->setFlash('error', 'This calendar is used other SNS Member.');

      $this->redirect('@calendar');
    }

    $this->opCalendarOAuth->saveAccessToken($member, $token);
    $this->opCalendarOAuth->savePrimaryId($member, $this->opCalendarOAuth->getPrimaryId($member, $token));

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
    $this->forwardUnless($calendar = $this->opCalendarOAuth->getCalendar(), 'calendarApi', 'index');

    $this->form = new opGoogleCalendarChoiceForm(null, array(
      'id' => $calendar->calendars->get('primary')->id,
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
