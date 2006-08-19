<?php
/**
 * Make a new package from scratch.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    CVS: $Id$
 * @since      File available since Release 0.4.0
 */

require_once 'PEAR/PackageFileManager/Frontend.php';
require_once 'Log.php';

session_start();

// logs only normal activity
$output = $_ENV['TMP'] . DIRECTORY_SEPARATOR . basename(__FILE__,'.php');
$logger = &Log::singleton('file', $output . '.log', '', array(), PEAR_LOG_INFO);

// build empty instance of PFM web frontend
$web =& PEAR_PackageFileManager_Frontend::singleton('Web', false, false, $logger);
// add ability to dump some informations for debugging with default class 'ActionDump'
$web->addActions(array('dump' => true));
// stop if serious error(s)
if ($web->hasErrors('error')) {
    $errors = $web->getErrors();
    echo '<pre>';
    var_dump($errors);
    echo '</pre>';
    die('exit on Error');
}
// run the wizard tabbed pages
$web->run();
?>