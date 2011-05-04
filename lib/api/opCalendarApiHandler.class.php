<?php
class opCalendarApiHandler
{
  const GET = 'GET';
  const POST = 'POST';
  const PUT = 'PUT';
  const DELETE = 'DELETE';

  private
    $timeout = 5,
    $retry = 5,

    $api = null,
    $apiResults = null;

  public function __construct(opCalendarApiInterface $api, opCalendarApiResultsInterface $apiResults)
  {
    $this->api = $api;
    $this->apiResults = $apiResults;
  }

  public function execute()
  {
    $this->accessApi(
      $this->api->getUrl(),
      $this->api->getMethod(),
      $this->api->getHeaders(),
      $this->api->getCookies(),
      $this->api->getPostvals()
    );

    try
    {
      $this->apiResults->parse();
    }
    catch (Exception $e)
    {
    }

    return $this->apiResults;
  }

  public function setTimeOut($time = 5)
  {
    $this->timeout = (int)$time;
  }

  public function set30xRetry($retry = 5)
  {
    $this->retry = (int)$retry;
  }

  private function accessApi($url, $method, $headers, $cookies, $postvals, $useHttpHeader = false)
  {
    static $count = 1;

    $ch = self::GET === $method ? curl_init() : curl_init($url);

    if ($headers)
    {
      curl_setopt($ch, CURLOPT_HTTPHEADER, is_array($headers) ? $headers : array($headers));
    }

    if (self::GET === $method)
    {
      curl_setopt($ch, CURLOPT_URL, $url);
    }
    else
    {
      curl_setopt($ch, CURLOPT_POSTFIELDS, $postvals);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }

    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_HEADER, (bool)$useHttpHeader);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    if ($cookies)
    {
      curl_setopt($ch, CURLOPT_COOKIE, $cookies);
    }

    $result = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (301 == $status_code || 302 == $status_code)
    {
      if ($count > $this->retry)
      {
        $this->apiResults->setHttpStatusCode($status_code);

        return false;
      }

      if (!$useHttpHeader)
      {
        $this->accessApi($url, $method, $headers, $postvals, $cookies, true);
      }
      else
      {
        if (preg_match('/Set-Cookie:(.*?);/i', $result, $matches))
        {
          $cookies = trim(array_pop($matches));
          $this->api->setCookies($cookies);
        }
        $count++;

        $this->accessApi($url, $method, $headers, $postvals, $cookies, false);
      }

      return;
    }

    $this->apiResults->setHttpStatusCode($status_code);
    $this->apiResults->setResult($result);
  }
}
