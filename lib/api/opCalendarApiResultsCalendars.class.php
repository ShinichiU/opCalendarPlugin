<?php
class opCalendarApiResultsCalendars extends opCalendarApiResultsXml
{
  public function toArray()
  {
    foreach ($this->xmlObject->entry as $value)
    {
      $contents = array();
      foreach ($value->content->attributes() as $k => $v)
      {
        $contents[(string)$k] = (string)$v;
      }
      $this->list[(string)$value->id]['title'] = (string)$value->title;
      $this->list[(string)$value->id]['contents'] = $contents;
    }

    return $this->list;
  }
}
