<?php
class opCalendarApiResultsXml extends opCalendarApiResults
{
  protected $xmlObject = null;

  public function parse()
  {
    $this->xmlObject = new SimpleXMLElement($this->result);
  }
}
