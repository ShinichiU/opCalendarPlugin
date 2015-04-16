<?php

/**
 * The opCalendarPluginConfiguration class.
 *
 * @package    opCalendarPluginConfiguration
 * @subpackage config
 * @author     Shinichi Urabe <urabe@tejimaya.com>
 */
class opCalendarPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    require_once dirname(__FILE__).'/../lib/vendor/google-api-php-client/autoload.php';
  }
}
