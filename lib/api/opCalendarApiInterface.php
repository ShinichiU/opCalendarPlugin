<?php

interface opCalendarApiInterface
{
  public function getUrl();

  public function getMethod();

  public function getHeaders();

  public function getCookies();

  public function getPostvals();
}
