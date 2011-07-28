<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opCalendarHelper
 *
 * @package    OpenPNE
 * @subpackage helper
 * @author     Shinichi Urabe <urabe@tejimaya.com>
 */

function get_auther_name($schedule_resource, $link_profile_page = false)
{
  if ($schedule_resource->member_id)
  {
    $member = $schedule_resource->Member;
    $value = $link_profile_page ? link_to($member->name, app_url_for('pc_frontend', '@member_profile?id='.$member->id), array('target' => '_blank')) : $member->name;

    return sprintf('[user] %s', $value);
  }
  if ($schedule_resource->admin_user_id)
  {
    return sprintf('[admin] %s', $schedule_resource->AdminUser->username);
  }

  return '';
}

function op_calendar_format_date($date, $format = 'd', $culture = null, $charset = null)
{
  if (!$culture)
  {
    $culture = sfContext::getInstance()->getUser()->getCulture();
  }

  switch ($format)
  {
  case 'XTime':
    switch ($culture)
    {
    case 'ja_JP':
      $tmpformat = 'HH時mm分';
      break;
    default:
      $tmpformat = 'HH:mm';
      break;
    }
    break;
  case 'XDate':
    switch ($culture)
    {
    case 'ja_JP':
      $tmpformat = 'yyyy年MM月dd日';
      break;
    default:
      $tmpformat = 'd';
      break;
    }
    break;
  }

  $result = op_format_date($date, $tmpformat, $calture, $charset);
  if (!$result && $format === 'XTime')
  {
    switch ($culture)
    {
    case 'ja_JP':
      $result = '--時--分';
      break;
    default:
      $result = '--:--';
      break;
    }
  }
  return $result;
}
