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

  $result = op_format_date($date, $tmpformat, $culture, $charset);
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

function op_link_to_schedule($schedule, $width = 40)
{
  $space = '&nbsp;';
  $icon = sprintf(
    '<span class="icon">%s%s</span>',
    image_tag('/opCalendarPlugin/images/icon_pen.gif', array('alt' => '[予]')).$space,
    $schedule->getApiIdUnique() ? image_tag(
      '//www.google.com/images/icons/product/calendar-16.png',
      array('raw_name' => true, 'alt' => 'Google Calendar')
    ).$space : ''
  );

  return link_to(
    $icon.op_truncate($schedule->title, $width, '...', 1),
    '@schedule_show?id='.$schedule->id
  );
}
