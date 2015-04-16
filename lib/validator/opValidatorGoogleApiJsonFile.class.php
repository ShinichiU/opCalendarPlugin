<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * opValidatorGoogleApiJsonFile validates a date
 *
 * @package    opCalendarPlugin
 * @subpackage validator
 * @author     Shinichi Urabe <urabe@tejimaya.com>
 */
class opValidatorGoogleApiJsonFile extends sfValidatorFile
{
  protected function doClean($value)
  {
    $file = parent::doClean($value);

    if ($file instanceof sfValidatedFile)
    {
      try
      {
        $client = new Google_Client();
        $client->setAuthConfigFile($file->getTempName());
      }
      catch (Google_Exception $e)
      {
        throw new sfValidatorError($this, (string) $e);
      }
    }

    return $file;
  }
}
