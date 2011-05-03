<?php
class opCalendarApiResultsStr extends opCalendarApiResults
{
  public function parse()
  {
    parse_str($this->result, $results);
    foreach ($results as $k => $v)
    {
      $this->list[$k] = $v;
    }
  }
}
