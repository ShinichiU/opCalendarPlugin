<?php
class opCalendarApiResultsStr extends opCalendarApiResults
{
  public function parse()
  {
    if ($this->is200StatusCode())
    {
      parse_str($this->result, $results);
      foreach ($results as $k => $v)
      {
        $this->list[$k] = $v;
      }
    }
  }
}
