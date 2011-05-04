<?php
class opCalendarApi implements opCalendarApiInterface
{
  private
    $OAuthRequest = null,
    $method = opCalendarApiHandler::GET,
    $isUseHeader = true,
    $parameters = array();

  public function __construct($consumer, $token, $http_method, $http_url, $parameters = array())
  {
    $this->OAuthRequest = OAuthRequest::from_consumer_and_token(
      $consumer, $token, $http_method, $http_url, $parameters
    );
    $this->OAuthRequest->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, $token);
    $this->method = $http_method;
    $this->parameters = is_array($parameters) ? $parameters : array($parameters);
  }

  public function getUrl($force = false)
  {
    $url = $this->OAuthRequest->get_normalized_http_url();
    if (!$this->isUseHeader)
    {
      return $this->OAuthRequest->to_url();
    }
    if (opCalendarApiHandler::GET !== $this->method && !$force)
    {
      return $url;
    }

    $uri = $this->getPostvals(true);

    return strpos($url, '?') ? $url.'&'.$uri : $url.'?'.$uri;
  }

  public function getMethod()
  {
    return $this->method;
  }

  public function getHeaders()
  {
    return $this->isUseHeader ? array($this->OAuthRequest->to_header(), 'Content-Type: application/atom+xml', 'GData-Version: 2') : null;
  }

  public function getCookies()
  {
    return sfContext::getInstance()->getUser()->getAttribute($this->getApiCookieSessionName(), null);
  }

  public function getPostvals($force = false)
  {
    if (opCalendarApiHandler::GET !== $this->method || $force)
    {
      if (!$this->parameters) return null;

      // Urlencode both keys and values
      $keys = OAuthUtil::urlencode_rfc3986(array_keys($this->parameters));
      $values = OAuthUtil::urlencode_rfc3986(array_values($this->parameters));
      $params = array_combine($keys, $values);

      // Parameters are sorted by name, using lexicographical byte value ordering.
      // Ref: Spec: 9.1.1 (1)
      uksort($params, 'strcmp');

      $pairs = array();
      foreach ($params as $parameter => $value)
      {
        if (is_array($value))
        {
          // If two or more parameters share the same name, they are sorted by their value
          // Ref: Spec: 9.1.1 (1)
          natsort($value);
          foreach ($value as $duplicate_value)
          {
            $pairs[] = $parameter . '=' . $duplicate_value;
          }
        }
        else
        {
          $pairs[] = $parameter . '=' . $value;
        }
      }
      // For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61)
      // Each name-value pair is separated by an '&' character (ASCII code 38)
      return implode('&', $pairs);
    }

    return null;
  }

  public function getApiCookieSessionName()
  {
    return 'opCalendarPlugin'.md5($this->OAuthRequest->get_normalized_http_url());
  }

  public function setCookies($cookie)
  {
    sfContext::getInstance()->getUser()->setAttribute($this->getApiCookieSessionName(), $cookie);
  }

  public function setIsUseHeader($bool = true)
  {
    $this->isUseHeader = (bool)$bool;
  }
}
