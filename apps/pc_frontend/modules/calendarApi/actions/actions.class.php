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
    $this->forward404Unless($this->opGoogleCalendarOAuth->isNeedRedirection());
    $request_token = $this->opGoogleCalendarOAuth->getRequestToken();
    $this->getUser()->setAttribute('opGoogleCalendarOAuthTokens', $request_token);

    $this->redirect($this->opGoogleCalendarOAuth->getAuthUrl($request_token['oauth_token']));
  }

 /**
  * Executes callback action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeCallback(sfWebRequest $request)
  {
    $this->forward404Unless($this->opGoogleCalendarOAuth->isNeedRedirection());
    $user = $this->getUser();
    $request_token = $user->getAttribute('opGoogleCalendarOAuthTokens');
    if (!$request_token)
    {
      $user->setFlash('error', '処理に失敗しました。もう一度やり直してください');
      $this->redirect('@homepage');
    }
    $access_token = $this->opGoogleCalendarOAuth
      ->getAccessToken($request->getParameter('oauth_verifier'), $request_token['oauth_token'], $request_token['oauth_token_secret']);
    $this->opGoogleCalendarOAuth->saveAccessToken($user->getMember(), $access_token['oauth_token'], $access_token['oauth_token_secret']);
    $user->setFlash('notice', 'Google Calendar API が利用出来るようになりました。');
    $user->getAttributeHolder()->remove('opGoogleCalendarOAuthTokens');
    $this->redirect('@calendar_api_import');
  }

 /**
  * Executes index action
  *
  * @param sfWebRequest $request A request object
  */
  public function executeImport(sfWebRequest $request)
  {
    $this->forwardIf($this->opGoogleCalendarOAuth->isNeedRedirection(), 'calendarApi', 'index');

    if (!$results = $this->opGoogleCalendarOAuth->getContents('default/owncalendars/full'))
    {
      return sfView::NONE;
    }

    $list = $results->toArray();
    $this->form = new opGoogleCalendarChoiceForm(null, array(
      'list' => $list,
      'opGoogleCalendarOAuth' => $this->opGoogleCalendarOAuth
    ));

    if ($request->isMethod(sfWebRequest::POST))
    {
      $this->form->bind($request->getParameter($this->form->getName()));
      if ($this->form->isValid())
      {
        $r = $this->form->save();

        $params = $request->getParameter($this->form->getName());
        $this->getUser()->setFlash('notice', $r ? 'カレンダーの読み込みに成功しました' : 'カレンダーの読み込みに一部失敗しました');
        $this->redirect(sprintf('@calendar_year_month?year=%04d&month=%02d', date('Y'), $params['months']));
      }
    }
  }
}
