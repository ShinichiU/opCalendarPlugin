<?php
class opCalendarApiResultsEvents extends opCalendarApiResultsXml
{
  public function toArray()
  {
    foreach ($this->xmlObject->entry as $value)
    {
      $this->list[(string)$value->id]['created_at'] = date('Y-m-d H:i:s', strtotime((string)$value->published));
      $this->list[(string)$value->id]['updated_at'] = date('Y-m-d H:i:s', strtotime((string)$value->updated));
      $this->list[(string)$value->id]['title'] = (string)$value->title;
      $this->list[(string)$value->id]['body'] = (string)$value->content;
    }

    return $this->list;
  }
}
