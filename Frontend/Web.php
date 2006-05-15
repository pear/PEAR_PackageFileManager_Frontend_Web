<?php
/**
 * A Web GUI frontend for the PEAR_PackageFileManager2 class.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    CVS: $Id$
 * @since      File available since Release 0.1.0
 */

if (!defined('PEAR_PACKAGEFILEMANAGER_FRONTEND_DATADIR')) {
    define('PEAR_PACKAGEFILEMANAGER_FRONTEND_DATADIR',
        '@data_dir@' . DIRECTORY_SEPARATOR .
        '@package_name@' . DIRECTORY_SEPARATOR);
}

require_once 'HTML/QuickForm/Controller.php';
require_once 'HTML/QuickForm/Action/Submit.php';
require_once 'HTML/QuickForm/Action/Jump.php';
require_once 'HTML/QuickForm/Action/Display.php';
require_once 'HTML/QuickForm/Action/Direct.php';
require_once 'PEAR/PackageFileManager/Frontend/Web/pages.php';

if (version_compare(phpversion(), '5.0.0', '<')) {
    require_once 'PHP/Compat.php';
    PHP_Compat::loadFunction('array_combine');
}

/**
 * A Web GUI frontend for the PEAR_PackageFileManager2 class.
 *
 * A Web frontend for the PEAR_PackageFileManager2 class.
 * It makes it easier for developers to create and maintain
 * PEAR package.xml files (versions 1.0 and 2.0).
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */

class PEAR_PackageFileManager_Frontend_Web extends PEAR_PackageFileManager_Frontend
{
    /**
     * Instance of a QF controller
     *
     * @var    object
     * @since  0.1.0
     * @access private
     */
    var $_qfc;

    /**
     * Constructor (ZE1)
     *
     * @param  mixed     $packagedirectory    Path to the base directory of the package
     * @param  mixed     $pathtopackagefile   Path to an existing package file to read in
     * @since  0.1.0
     * @access public
     */
    function PEAR_PackageFileManager_Frontend_Web($driver, $packagedirectory, $pathtopackagefile)
    {
        $this->__construct($driver, $packagedirectory, $pathtopackagefile);
    }

    /**
     * Constructor (ZE2)
     *
     * @param  mixed     $packagedirectory    Path to the base directory of the package
     * @param  mixed     $pathtopackagefile   Path to an existing package file to read in
     * @since  0.1.0
     * @access protected
     */
    function __construct($driver, $packagedirectory, $pathtopackagefile)
    {
        parent::PEAR_PackageFileManager_Frontend($driver, $packagedirectory, $pathtopackagefile);

        // build a new non-modal controller
        $this->_qfc = new HTML_QuickForm_Controller($driver, false);
    }

    /**
     * Adds all pages of wizard at once
     *
     * @access public
     * @since  0.1.0
     */
    function addPages()
    {
         $pages = $this->getOption(array('settings', 'gui', 'pages'), false);

         foreach($pages['pages']['page'] as $page) {
             $pageN = array('settings', 'gui', 'pages', array('page', array('id' => $page['@']['id'])));
             $this->addPage($pageN);
         }
    }

    /**
     * Add a specific page to wizard or each page one by one
     *
     * @access public
     * @since  0.1.0
     */
    function addPage($pagePath)
    {
        $page = $this->getOption($pagePath, false);
        if ($this->hasErrors()) {
            return;
        }

        $page = array_values($page);
        $className = $page[0]['@']['class'];
        $pageName  = $page[0]['@']['name'];

        $qfcPage =& new $className($pageName);
        $this->_qfc->addPage($qfcPage);

        // adds additional action
        foreach ($page[0] as $action => $attr) {
            if ($action == '#' || $action == '@') {
                continue;
            }
            $qfcPage->addAction($action, new $attr['@']['class']);
        }

        // adds common action on each page
        $this->_qfc->addAction($pageName, new HTML_QuickForm_Action_Direct());
    }

    /**
     * Adds common actions for the frontend wizard
     *
     * @access public
     * @since  0.1.0
     */
    function addActions()
    {
        // adds display driver
        $ActionDisplay = $this->getOption(array('settings','gui','actions','display'));
        if (!class_exists($ActionDisplay)) {
            include_once 'PEAR/PackageFileManager/Frontend/Web/Default.php';
            $ActionDisplay = 'ActionDisplay';
        }
        $this->_qfc->addAction('display', new $ActionDisplay() );

        // adds basic actions (abort, commit, reset)
        $ActionProcess = $this->getOption(array('settings','gui','actions','process'));
        if (!class_exists($ActionProcess)) {
            include_once 'PEAR/PackageFileManager/Frontend/Web/Process.php';
            $ActionProcess = 'ActionProcess';
        }
        $this->_qfc->addAction('abort',  new $ActionProcess() );
        $this->_qfc->addAction('commit', new $ActionProcess() );
        $this->_qfc->addAction('reset',  new $ActionProcess() );

        // adds dump class action (if necessary)
        $ActionDump = $this->getOption(array('settings','gui','actions','dump'));
        if ($ActionDump) {
            if (!class_exists($ActionDump)) {
                include_once 'PEAR/PackageFileManager/Frontend/Web/Dump.php';
                $ActionDump = 'ActionDump';
            }
            $this->_qfc->addAction('dump', new $ActionDump() );
        }
    }

    /**
     * Applies all defaults
     *
     * @access public
     * @since  0.1.0
     */
    function applyDefaults()
    {
        $sess =& $this->container();

        $hasDefaults = (count($sess['defaults']) > 0);
        if ($hasDefaults) {
            $defaults = $sess['defaults'];
        } else {
            $settings = $this->getOption(array('settings','gui','pages'), false);
            $defaults = array();
            foreach($settings['pages']['page'] as $page) {
                $def = $this->getDefaults($page['@']['id']);
                if (is_array($def)) {
                    $defaults = array_merge($defaults, $def);
                }
            }
        }
        $this->_qfc->setDefaults($defaults);
    }

    /**
     * Processes the request.
     *
     * @access public
     * @since  0.1.0
     */
    function run()
    {
        $this->applyDefaults();
        $this->_qfc->run();
    }
}
?>