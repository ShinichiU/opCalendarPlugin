<?php
abstract class opCalendarApiResultsXml extends opCalendarApiResults
{
  protected
    $xmlObject = null,
    $dummyXml = '<?xml version="1.0" encoding="utf-8" ?><entry></entry>';

  public function parse()
  {
    if ($this->is200StatusCode() && preg_match('/(<\?xml.*)$/s', $this->result, $matches))
    {
      $this->xmlObject = new SimpleXMLElement($matches[1]);
    }
    else
    {
      $this->xmlObject = new SimpleXMLElement($this->dummyXml);
    }
  }
}
