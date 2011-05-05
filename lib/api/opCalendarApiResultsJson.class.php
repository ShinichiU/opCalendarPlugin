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
        $this->list = isset($results['data']['items']) ? $results['data']['items'] : array();
        $this->author_email = isset($results['data']['author']['email']) ? $results['data']['author']['email'] : null;
      }
    }
  }
}
