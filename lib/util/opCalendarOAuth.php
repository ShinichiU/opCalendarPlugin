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
    $this->client->setApprovalPrompt('force');
  }

  public function getClient()
  {
    return $this->client;
  }

  public function saveAccessToken(Member $member = null, $token)
  {
    $this->getMember($member)->setConfig(self::ACCESS_TOKEN_KEY, $token);
  }

  public function savePrimaryId(Member $member = null, $id)
  {
    $this->getMember($member)->setConfig('opCalendarPlugin_email', $id);
  }

  public function findAccessToken(Member $member = null)
  {
    return $this->getMember($member)->getConfig(self::ACCESS_TOKEN_KEY);
  }

  public function authenticate(Member $member = null, $token = null)
  {
    if (!$token && !($token = $this->findAccessToken($member)))
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

  public function getCalendar(Member $member = null, $token = null)
  {
    if (!$this->authenticate($member, $token))
    {
      return false;
    }

    return new Google_Service_Calendar($this->getClient());
  }

  public function getPrimaryId(Member $member = null, $token = null)
  {
    if (!$calendar = $this->getCalendar($member, $token))
    {
      return false;
    }

    return $calendar->calendars->get('primary')->id;
  }

  public function isAlreadyUsedCalendarId(Member $member = null, $token = null)
  {
    $member = $this->getMember($member);
    $id = $this->getPrimaryId($member, $token);

    $memberId = opCalendarPluginToolkit::seekEmailAndGetMemberId($id);

    return $memberId && (int)$member->id !== (int)$memberId;
  }

  private function getMember(Member $member = null)
  {
    if (null === $member)
    {
      $member = sfContext::getInstance()->getUser()->getMember();
    }

    return $member;
  }
}
