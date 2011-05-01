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
    $default_consumer = null;

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
    $this->default_consumer = new OAuthConsumer(
      opConfig::get('op_calendar_google_data_api_key', 'anonymous'),
      opConfig::get('op_calendar_google_data_api_secret', 'anonymous')
    );
  }

  public function getRequestToken()
  {
    $req = OAuthRequest::from_consumer_and_token(
      $this->default_consumer,
      NULL,
      'GET',
      self::REQUEST_TOKEN_ENDPOINT,
      array(
        'oauth_callback' => app_url_for('pc_frontend', '@calendar_api_callback', true),
        'scope' => self::SCOPE,
      )
    );

    $req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $this->default_consumer, NULL);

    return $this->parse_str_curl($req->to_url(), 'GET');
  }

  public function getAuthUrl($oauth_token)
  {
    return sprintf('%s?oauth_token=%s', self::AUTHORIZE_ENDPOINT, $oauth_token);
  }

  public function getAccessToken($oauth_verifier, $oauth_token, $ouath_token_secret)
  {
    $params = array('oauth_verifier' => $oauth_verifier);
    $final_consumer = new OAuthConsumer($oauth_token, $ouath_token_secret);
    $acc_req = OAuthRequest::from_consumer_and_token(
      $this->default_consumer,
      $final_consumer,
      'GET',
      self::OAUTH_ACCESS_TOKEN_ENDPOINT,
      $params
    );
    $acc_req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $this->default_consumer, $final_consumer);

    return $this->parse_str_curl($acc_req->to_url());
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
    $url = self::SCOPE.'default/allcalendars/full';
    $access_consumer = new OAuthConsumer($oauth_token, $oauth_token_secret);
    $acc_req = OAuthRequest::from_consumer_and_token(
      $this->default_consumer,
      $access_consumer,
      'GET',
      $url
    );
    $acc_req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $this->default_consumer, $access_consumer);
    $this->curl($url, 'GET', array($acc_req->to_header()));

    return 200 == self::$last_status_code;
  }

  private function parse_str_curl($url, $method = 'GET', $headers = array(), $postvals = null, $cookie = null)
  {
    $response = $this->curl($url, $method, $headers, $postvals);

    if ($response)
    {
      parse_str($response, $results);

      return $results;
    }

    return false;
  }

  private function curl($url, $method = 'GET', $headers = array(), $postvals = null, $cookie = null)
  {
    static $count = 0;

    $ch = 'GET' === $method ? curl_init() : curl_init($url);

    if ($headers)
    {
      $headers = is_array($headers) ? $headers : array($headers);
      curl_setopt($ch, CURLOPT_HEADER, true);
      curl_setopt($ch, CURLINFO_HEADER_OUT, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers + array('Content-Type: application/atom+xml'));
    }

    if ($method == 'GET')
    {
      curl_setopt($ch, CURLOPT_URL, $url);
    }
    else
    {
      curl_setopt($ch, CURLOPT_VERBOSE, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $postvals);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
      curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
    if ($cookie)
    {
      curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    }

    $response = curl_exec($ch);
    self::$last_status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (301 == self::$last_status_code || 302 == self::$last_status_code)
    {
      if ($count > 5)
      {
        return false;
      }
      preg_match('/Set-Cookie:(.*?)\n/', $response, $matches);
      $cookie = trim(array_pop($matches));
      $count++;
      $this->curl($url, $method , $headers, $postvals, $cookie);
    }

    return 200 == self::$last_status_code ? $response : false;
  }
}
