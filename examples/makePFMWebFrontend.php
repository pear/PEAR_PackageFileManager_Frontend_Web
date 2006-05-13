<?php
/**
 * Make the PFM Web frontend package.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    CVS: $Id$
 * @since      File available since Release 0.1.0
 */

require_once 'PEAR/PackageFileManager/Frontend.php';

session_start();

// configuration options
$config = array('makePFMFrontend.xml', 'XML');

// where to find package sources
$pkgDir = 'D:/php/pear/PEAR_PackageFileManager_Frontend_Web/package2.xml';

$web =& PEAR_PackageFileManager_Frontend::singleton('Web', $pkgDir);
$web->loadPreferences($config);
$web->addPages();
$web->addActions();
if ($web->hasErrors('error')) {
    $errors = $web->getErrors();
    echo '<pre>';
    var_dump($errors);
    echo '</pre>';
    die('exit on Error');
}
$web->run();
?>