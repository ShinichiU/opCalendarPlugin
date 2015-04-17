<?php

require_once('OAuth.php');

class opCalendarOAuth
{
  const ACCESS_TOKEN_KEY = 'google_calendar_oauth_access_token';

  protected static
    $instance = null,
    $last_status_code = null;

  protected
    $client = null;

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

    $this->buildClient();
  }

  protected function buildClient()
  {
    $this->client = new Google_Client();
    $json = new opGoogleOAuthJson;
    $this->client->setAuthConfig((string) $json);
    $this->client->addScope(Google_Service_Calendar::CALENDAR);
    $this->client->setAccessType('offline');
  }

  public function getClient()
  {
    return $this->client;
  }

  public function saveAccessToken(Member $member = null, $token)
  {
    if (null === $member)
    {
      $member = sfContext::getInstance()->getUser()->getMember();
    }

    $member->setConfig(self::ACCESS_TOKEN_KEY, $token);
  }

  public function findAccessToken(Member $member = null)
  {
    if (null === $member)
    {
      $member = sfContext::getInstance()->getUser()->getMember();
    }
    if (!$member || !$member->id)
    {
      return null;
    }

    return $member->getConfig(self::ACCESS_TOKEN_KEY);
  }

  public function authenticate(Member $member = null)
  {
    if (!$token = $this->findAccessToken($member))
    {
      return false;
    }

    $this->client->setAccessToken($token);
    if (!$this->client->isAccessTokenExpired())
    {
      return true;
    }

    $this->client->refreshToken($this->client->getRefreshToken());
    $token = $this->client->getAccessToken();
    $this->saveAccessToken($member, $token);

    return !$this->client->isAccessTokenExpired();
  }
}
