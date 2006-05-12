<?php
/**
 * Global common actions to perform from any page of the frontend.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    CVS: $Id$
 * @since      File available since Release 0.1.0
 */

/**
 * Global common actions to perform from any page of the frontend.
 *
 * These actions are :
 * - 'abort'  Quit the frontend without doing any changes on package(2).xml file(s).
 *            Ask a confirmation.
 * - 'commit' Apply all changes to package(2).xml file(s) before leaving the frontend.
 *            Ask a confirmation.
 * - 'reset'  Retrieve defaults tab data before you made changes.
 *            Ask a confirmation. Only the current tab data are lost (not the other ones).
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class ActionProcess extends HTML_QuickForm_Action
{
    /**
     * Performs an action on a page of the controller (wizard)
     *
     * @param  string   $page          current page displayed by the controller
     * @param  string   $actionName    page action asked
     * @return void
     * @since  0.1.0
     * @access public
     */
    function perform(&$page, $actionName)
    {
        $fe =& PEAR_PackageFileManager_Frontend::singleton();
        $pageName = $page->getAttribute('id');
        $fe->log('debug',
            str_pad($pageName .'('. __LINE__ .')', 20, '.') .
            ' ActionProcess='. $actionName
        );

        switch ($actionName) {
            case 'abort':
                echo '<h1>Task was canceled</h1>';
                echo '<p>No package was created or modified.</p>';
                $fe->container(true);
                die();

            case 'commit':
                $exportV1 = is_null($page->getSubmitValue('exportCompatibleV1')) ? false : true;
                $changelogOldToNew =  is_null($page->getSubmitValue('changelogOldToNew')) ? false : true;
                $simpleOutput = is_null($page->getSubmitValue('simpleOutput')) ? false : true;

                ob_start();
                $filename = $fe->buildPackageFile(null, $exportV1, $changelogOldToNew, $simpleOutput);
                ob_end_clean();
                // reset session data
                $fe->container(true);

                echo '<h1>Task was proceed</h1>';
                echo "<p>New package file is available at <b>$filename</b>.</p>";
                die();

            case 'reset':
                $page->loadValues(null);
                $page->applyDefaults();
                $page->controller->applyDefaults($pageName);
                return $page->handle('display');
        }
    }
}
?>