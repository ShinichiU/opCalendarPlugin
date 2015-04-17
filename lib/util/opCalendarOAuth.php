<?php

class opCalendarOAuth
{
  const ACCESS_TOKEN_KEY = 'google_calendar_oauth_access_token';

  private static
    $instance = null;

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

  private function __construct()
  {
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
    $this->saveAccessToken($member, $this->client->getAccessToken());

    return !$this->client->isAccessTokenExpired();
  }
}
