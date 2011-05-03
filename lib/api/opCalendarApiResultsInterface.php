<?php

interface opCalendarApiResultsInterface
{
  public function setHttpStatusCode($code);

  public function setResult($result);

  public function parse();
}
