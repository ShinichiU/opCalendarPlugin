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
