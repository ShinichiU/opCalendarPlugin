<?php
class opCalendarApiResultsJson extends opCalendarApiResults
{
  protected $author_email = null;

  public function parse()
  {
    if ($this->is200StatusCode())
    {
      if (preg_match('/({.*})$/s', $this->result, $matches))
      {
        $results = json_decode($matches[1], true);
        $this->list = $results['data']['items'];
        $this->author_email = $results['data']['author']['email'];
      }
    }
  }
}
