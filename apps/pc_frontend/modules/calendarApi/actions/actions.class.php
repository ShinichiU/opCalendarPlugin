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
    $this->redirect('@homepage');
  }
}
