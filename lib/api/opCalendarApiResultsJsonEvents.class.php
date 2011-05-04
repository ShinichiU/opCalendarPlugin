<?php
class opCalendarApiResultsJsonEvents extends opCalendarApiResultsJson
{
  protected static $cache_api_id_unique = array();

  public function toArray()
  {
    $results = array();
    foreach ($this->list as $key => $value)
    {
      if ('confirmed' != $value['status'] || $value['creator']['email'] != $this->author_email)
      {
        continue;
      }

      $results[$key]['need_convert']['creator_email'] = $value['creator']['email'];
      foreach ($value['attendees'] as $k => $v)
      {
        $results[$key]['need_convert']['ScheduleMember'][$k]['email'] = $v['email'];
      }

      $results[$key]['title'] = $value['title'] ? $value['title'] : '(no title)';
      $results[$key]['body'] = $value['details'] ? $value['details'] : '(no body)';
      $results[$key]['start_date'] = date('Y-m-d', strtotime($value['when'][0]['start']));
      $results[$key]['start_time'] = date('H:i:s', strtotime($value['when'][0]['start']));
      $results[$key]['end_date'] = date('Y-m-d', strtotime($value['when'][0]['end']) - 1);
      $results[$key]['end_time'] = date('H:i:s', strtotime($value['when'][0]['end']) - 1);
      $results[$key]['api_flag'] = ScheduleTable::GOOGLE_CALENDAR;
      $results[$key]['api_id_unique'] = $value['id'];
      self::$cache_api_id_unique[$value['id']] = $key;
      $results[$key]['api_etag'] = $value['etag'];
      $results[$key]['created_at'] = date('Y-m-d H:i:s', strtotime($value['created']));
      $results[$key]['updated_at'] = date('Y-m-d H:i:s', strtotime($value['updated']));
    }

    foreach ($results as $k => $v)
    {
      $ids = explode('_', $v['api_id_unique']);
      if (isset($ids[1]) && isset(self::$cache_api_id_unique[$ids[0]]))
      {
        unset($results[self::$cache_api_id_unique[$ids[0]]]);
      }
    }
    $this->list = $results;

    return $results;
  }
}
