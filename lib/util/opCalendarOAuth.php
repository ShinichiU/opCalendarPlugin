<?php

require_once('OAuth.php');

class opGoogleCalendarOAuth
{
  const REQUEST_TOKEN_ENDPOINT = 'https://www.google.com/accounts/OAuthGetRequestToken';
  const AUTHORIZE_ENDPOINT = 'https://www.google.com/accounts/OAuthAuthorizeToken';
  const OAUTH_ACCESS_TOKEN_ENDPOINT = 'https://www.google.com/accounts/OAuthGetAccessToken';
  const SCOPE = 'https://www.google.com/calendar/feeds/';

  protected static
    $instance = null,
    $last_status_code = null;

  protected
    $consumer = null;

  public static function getInstance()
  {
    if (null === self::$instance)
    {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function __construct()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('opUtil');
    $this->consumer = new OAuthConsumer(
      opConfig::get('op_calendar_google_data_api_key', 'anonymous'),
      opConfig::get('op_calendar_google_data_api_secret', 'anonymous')
    );
  }

  public function getRequestToken()
  {
    $api = new opCalendarApi($this->consumer, null, opCalendarApiHandler::GET,
      self::REQUEST_TOKEN_ENDPOINT,
      array(
        'oauth_callback' => app_url_for('pc_frontend', '@calendar_api_callback', true),
        'scope' => self::SCOPE,
      )
    );
    $api->setIsUseHeader(false);
    $handler = new opCalendarApiHandler($api, new opCalendarApiResultsStr());

    return $handler->execute();
  }

  public function getAuthUrl($oauth_token)
  {
    return sprintf('%s?oauth_token=%s', self::AUTHORIZE_ENDPOINT, $oauth_token);
  }

  public function getAccessToken($oauth_verifier, $oauth_token, $ouath_token_secret)
  {
    $api = new opCalendarApi($this->consumer, new OAuthConsumer($oauth_token, $ouath_token_secret), opCalendarApiHandler::GET,
      self::OAUTH_ACCESS_TOKEN_ENDPOINT,
      array('oauth_verifier' => $oauth_verifier)
    );
    $api->setIsUseHeader(false);
    $handler = new opCalendarApiHandler($api, new opCalendarApiResultsStr());

    return $handler->execute();
  }

  public function saveAccessToken(Member $member, $oauth_token, $ouath_token_secret)
  {
    Doctrine_Core::getTable('MemberConfig')
      ->setValue($member->id, 'google_calendar_oauth_token', $oauth_token);
    Doctrine_Core::getTable('MemberConfig')
      ->setValue($member->id, 'google_calendar_oauth_token_secret', $ouath_token_secret);

  }

  public function getAccessTokenDb(Member $member = null)
  {
    if (null === $member)
    {
      $member = sfContext::getInstance()->getUser()->getMember();
    }
    if (!$member || !$member->id)
    {
      return null;
    }

    $token = Doctrine_Core::getTable('MemberConfig')
      ->retrieveByNameAndMemberId('google_calendar_oauth_token', $member->id);
    $secret = Doctrine_Core::getTable('MemberConfig')
      ->retrieveByNameAndMemberId('google_calendar_oauth_token_secret', $member->id);

    if (!$token || !$secret)
    {
      return null;
    }

    return array('oauth_token' => $token->getValue(), 'oauth_token_secret' => $secret->getValue());
  }

  public function isNeedRedirection(Member $member = null)
  {
    $token = $this->getAccessTokenDb($member);

    if (null === $token || !$token['oauth_token'] || !$token['oauth_token_secret'])
    {
      return true;
    }

    return !$this->isActiveAccessTocken($token['oauth_token'], $token['oauth_token_secret']);
  }

  private function isActiveAccessTocken($oauth_token, $oauth_token_secret)
  {
    $api = new opCalendarApi(
      $this->consumer,
      new OAuthConsumer($oauth_token, $oauth_token_secret),
      opCalendarApiHandler::GET,
      self::SCOPE.'default/allcalendars/full'
    );
    $handler = new opCalendarApiHandler($api, new opCalendarApiResultsXml());

    return $handler->execute()->is200StatusCode();
  }
}
