<?php
class opCalendarApi implements opCalendarApiInterface
{
  private
    $OAuthRequest = null,
    $method = opCalendarApiHandler::GET,
    $isUseHeader = true;

  public function __construct($consumer, $token, $http_method, $http_url, $parameters = null)
  {
    $this->OAuthRequest = OAuthRequest::from_consumer_and_token(
      $consumer, $token, $http_method, $http_url, $parameters
    );
    $this->OAuthRequest->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, $token);
    $this->method = $http_method;
  }

  public function getUrl()
  {
    if ($this->isUseHeader || opCalendarApiHandler::GET !== $this->method)
    {
      return $this->OAuthRequest->get_normalized_http_url();
    }

    return $this->OAuthRequest->to_url();
  }

  public function getMethod()
  {
    return $this->method;
  }

  public function getHeaders()
  {
    return $this->isUseHeader ? array($this->OAuthRequest->to_header(), 'Content-Type: application/atom+xml') : null;
  }

  public function getCookies()
  {
    return sfContext::getInstance()->getUser()->getAttribute('G-Cookie', null);
  }

  public function getPostvals()
  {
    if (opCalendarApiHandler::GET !== $this->method)
    {
      return $this->OAuthRequest->to_postdata();
    }

    return null;
  }

  public function setIsUseHeader($bool = true)
  {
    $this->isUseHeader = (bool)$bool;
  }
}
