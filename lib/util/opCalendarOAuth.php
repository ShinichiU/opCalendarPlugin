<?php

require_once('OAuth.php');

class opGoogleCalendarOAuth
{
  const REQUEST_TOKEN_ENDPOINT = 'https://www.google.com/accounts/OAuthGetRequestToken';
  const AUTHORIZE_ENDPOINT = 'https://www.google.com/accounts/OAuthAuthorizeToken';
  const OAUTH_ACCESS_TOKEN_ENDPOINT = 'https://www.google.com/accounts/OAuthGetAccessToken';
  const SCOPE = 'http://www.google.com/calendar/feeds/';

  protected static $instance = null;

  public static function getInstance()
  {
    if (null === self::$instance)
    {
      self::$instance = new __CLASS__;
    }

    return self::$instance;
  }

  public function __construct()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers('opUtil');
  }

  public function getRequestToken()
  {
    $consumer = new OAuthConsumer(
      opConfig::get('op_calendar_google_data_api_key'),
      opConfig::get('op_calendar_google_data_api_secret')
    );

    $req = OAuthRequest::from_consumer_and_token(
      $consumer,
      NULL,
      'GET',
      self::REQUEST_TOKEN_ENDPOINT,
      // TODO: あとで戻す
      //array('oauth_callback' => app_url_for('pc_frontend', '@calendar_api_callback', true), 'site' => 'http://nuts-choco.com')
      array(
        'oauth_callback' => 'http://nuts-choco.com/calendarApi/callback',
        'scope' => self::SCOPE,
      )
    );

    $req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $this->consumer, NULL);

    return $this->curl($req->to_url(), 'GET');
  }

  public function getAuthUrl($oauth_token)
  {
    return sprintf('%s?oauth_token=%s', self::AUTHORIZE_ENDPOINT, $oauth_token);
  }

  public function getAccessTocken($oauth_verifier, $oauth_token, $ouath_token_secret)
  {
    $params = array('oauth_verifier' => $oauth_verifier);
    $consumer = new OAuthConsumer('anonymous', 'anonymous');
    $final_consumer = new OAuthConsumer($oauth_token, $ouath_token_secret);
    $acc_req = OAuthRequest::from_consumer_and_token($consumer, $final_consumer, 'GET', self::OAUTH_ACCESS_TOKEN_ENDPOINT, $params);
    $acc_req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, $final_consumer);

    $result = $this->curl($acc_req->to_url(), 'GET');
var_dump($result);

    $token = Doctrine_Core::getTable('MemberConfig')->retrieveByNameAndMemberId('google_calendar_oauth_token', $id);
    $secret = Doctrine_Core::getTable('MemberConfig')->retrieveByNameAndMemberId('google_calendar_oauth_token_secret', $id);

    return array('token' => $token, 'secret' => $secret);
  }

  public function getAccessTockenDb($id = null)
  {
    if (null === $id)
    {
      $id = sfContext::getInstance()->getUser()->getMemberId();
    }
    if (!$id)
    {
      return null;
    }

    $token = Doctrine_Core::getTable('MemberConfig')->retrieveByNameAndMemberId('google_calendar_oauth_token', $id);
    $secret = Doctrine_Core::getTable('MemberConfig')->retrieveByNameAndMemberId('google_calendar_oauth_token_secret', $id);

    return array('oauth_token' => $token, 'oauth_token_secret' => $secret);
  }

  public function isNeedRedirection()
  {
    $token = $this->getAccessTokenDb();

    if (empty($token)) return true;

//    /* Create a TwitterOauth object with consumer/user tokens. */
//    $connection = new TwitterOAuth(
//      $this->consumer_key,
//      $this->consumer_secret,
//      $token['key_string'],
//      $token['secret']
//    );  
//
//    /* 認証テストを行う */
//    $res = $connection->get('account/verify_credentials');
//
    return isset($res->error);
  }

  public function curl($url, $method = 'GET', $headers = null, $postvals = null)
  {
    $ch = curl_init($url);

    if ($method == 'GET')
    {
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    }
    else
    {
      curl_setopt_array($ch, array(
        CURLOPT_HEADER => true,
        CURLINFO_HEADER_OUT => true,
        CURLOPT_VERBOSE => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => $postvals,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_TIMEOUT => 3
      ));
    }

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if (200 == $http_code)
    {
      parse_str($response, $results);

      return $results;
    }
    else
    {
      return false;
    }
  }
}
