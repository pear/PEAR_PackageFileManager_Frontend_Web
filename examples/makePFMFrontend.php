<?php
/**
 * Make the PFM frontend package.
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

// logs all activity
$output = $_ENV['TMP'] . DIRECTORY_SEPARATOR . basename(__FILE__,'.php');
$logger = &Log::singleton('file', $output . '.log');

// where to find package sources
$pkgDir = 'D:/php/pear/PEAR_PackageFileManager_Frontend/package2.xml';

$web =& PEAR_PackageFileManager_Frontend::singleton('Web', $pkgDir, false, $logger);
// configuration options
$web->setOption('baseinstalldir', 'PEAR/PackageFileManager');
$web->setOption('exportcompatiblev1', true);
$web->setOption('changelogoldtonew', false);
$web->setOption('simpleoutput', true);
$web->setOption('outputdirectory', 'd:/temp');
$web->setOption('filelistgenerator', 'cvs');

// add ability to dump some informations for debugging
$web->addActions(array('dump' => 'ActionDump'));

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