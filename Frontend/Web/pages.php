<?php
/**
 * Common wizard presentation.
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
 * Common wizard presentation.
 *
 * Creates all tabs and buttons as common layout for the frontend.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class TabbedPage extends HTML_QuickForm_Page
{
    /**
     * Builds tabs of the Wizard.
     *
     * @return void
     * @since  0.1.0
     * @access public
     */
    function buildTabs()
    {
        $this->_formBuilt = true;
        $this->setRequiredNote('<span class="required">*</span><span class="note"> denotes required field</span>');

        // Here we get all page names in the controller
        $pages  = array();
        $myName = $current = $this->getAttribute('id');
        while (null !== ($current = $this->controller->getPrevName($current))) {
            $pages[] = $current;
        }
        $pages = array_reverse($pages);
        $pages[] = $current = $myName;
        while (null !== ($current = $this->controller->getNextName($current))) {
            $pages[] = $current;
        }
        // Here we display buttons for all pages, the current one's is disabled
        foreach ($pages as $pageName) {
            $tabs[] = $this->createElement(
                        'submit', $this->getButtonName($pageName), ucfirst($pageName),
                        array('class' => 'flat') + ($pageName == $myName? array('disabled' => 'disabled'): array())
                      );
        }
        $this->addGroup($tabs, 'tabs', null, '&nbsp;', false);
    }

    /**
     * Builds command buttons of the Wizard.
     *
     * @return void
     * @since  0.1.0
     * @access public
     */
    function buildButtons($disable = null, $commands = null)
    {
        $buttons = array('abort', 'commit', 'reset', 'dump');
        if (isset($commands)) {
            $buttons = array_merge($buttons, $commands);
        }

        if (!isset($disable)) {
            $disable = array();
        } elseif (!isset($disable[0])) {
            $disable = array($disable);
        }

        $confirm = $attributes = array('class' => 'cmdButton');
        $confirm['onclick'] = "return(confirm('Are you sure ?'));";

        $prevnext = array();

        foreach ($buttons as $event) {
            switch ($event) {
                case 'abort':
                case 'commit':
                case 'reset':
                case 'remove':
                    $type = 'submit';
                    $attrs = $confirm;
                    break;
                default :
                    $type = 'submit';
                    $attrs = $attributes;
                    break;
            }
            if (in_array($event, $disable)) {
                $attrs['disabled'] = 'true';
            }
            if ($event == 'dump') {
                $fe =& PEAR_PackageFileManager_Frontend::singleton();
                $dump = $fe->_actions['dump'];
                if ($dump === false) {
                    continue;
                }
                $opts = array(
                    '1' => 'PFM FE options',
                    '2' => 'Forms values container', '3' => 'Warnings Stack',
                    '4' => 'Included Files', '5' => 'PFM package info',
                    '6' => 'Declared Classes'
                    );
                $prevnext[] =&HTML_QuickForm::createElement('select', 'dumpOption', '', $opts);
            }
            $prevnext[] =&HTML_QuickForm::createElement($type, $this->getButtonName($event), ucfirst($event), HTML_Common::_getAttrString($attrs));
        }
        $this->addGroup($prevnext, 'buttons', '', '&nbsp;', false);
    }

    /**
     * Abstract implementation of the method that set default values for a page
     * of the Wizard.
     *
     * @return void
     * @since  0.1.0
     */
    function applyDefaults()
    {
    }
}

require_once 'PEAR/PackageFileManager/Frontend/Web/PackagePage.php';
require_once 'PEAR/PackageFileManager/Frontend/Web/ReleasePage.php';
require_once 'PEAR/PackageFileManager/Frontend/Web/MaintainersPage.php';
require_once 'PEAR/PackageFileManager/Frontend/Web/DependenciesPage.php';
require_once 'PEAR/PackageFileManager/Frontend/Web/ReplacementsPage.php';
require_once 'PEAR/PackageFileManager/Frontend/Web/RolesPage.php';
require_once 'PEAR/PackageFileManager/Frontend/Web/ExceptionsPage.php';
require_once 'PEAR/PackageFileManager/Frontend/Web/PreviewPage.php';
require_once 'PEAR/PackageFileManager/Frontend/Web/ErrorsPage.php';

if (isset($_GET['arrow_ltr'])) {
    // Package data directory
    $data_dir = '@data_dir@' . DIRECTORY_SEPARATOR
              . '@package_name@' . DIRECTORY_SEPARATOR;

    $filename = $data_dir . 'arrow_ltr.gif';
    header('content-type: image/gif');
    readfile($filename);
    exit();
}
?>