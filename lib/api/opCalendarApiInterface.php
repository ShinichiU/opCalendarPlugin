<?php

interface opCalendarApiInterface
{
  public function getUrl($force = false);

  public function getMethod();

  public function getHeaders();

  public function getCookies();

  public function getPostvals($force = false);

  public function setCookies($cookie);
}
