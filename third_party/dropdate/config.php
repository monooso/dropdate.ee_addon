<?php

/**
 * DropDate NSM Add-on Updater information.
 *
 * @author      Stephen Lewis (http://github.com/experience/)
 * @copyright   Experience Internet
 * @package     DropDate
 * @version     2.2.0
 */

if ( ! defined('DROPDATE_NAME'))
{
  define('DROPDATE_NAME', 'DropDate');
  define('DROPDATE_VERSION', '2.2.0');
  define('DROPDATE_DESCRIPTION', 'Fieldtype enabling users to select a date using 3 or 5 drop-downs (day, month, year[, hour, minute]).');
}

$config['name']     = DROPDATE_NAME;
$config['version']  = DROPDATE_VERSION;
$config['nsm_addon_updater']['versions_xml']
  = 'http://experienceinternet.co.uk/software/feeds/dropdate';

/* End of file      : config.php */
/* File location    : third_party/dropdate/config.php */
