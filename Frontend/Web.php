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
 * @version    Release: 0.2.0
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
     * All default wizard pages definition
     *
     * @var    array
     * @since  0.3.0
     * @access private
     */
    var $_pages = array(
        array('@' => array(
                  'class' => 'PackagePage',
                  'id' => 'page1',
                  'name' => 'Package')
            ),
        array('@' => array(
                  'class' => 'ReleasePage',
                  'id' => 'page2',
                  'name' => 'Release')
            ),
        array('@' => array(
                  'class' => 'MaintainersPage',
                  'id' => 'page3',
                  'name' => 'Maintainers'),
              'drop'   => array('@' => array('class' => 'MaintainersPageAction')),
              'add'    => array('@' => array('class' => 'MaintainersPageAction')),
              'edit'   => array('@' => array('class' => 'MaintainersPageAction')),
              'save'   => array('@' => array('class' => 'MaintainersPageAction')),
              'cancel' => array('@' => array('class' => 'MaintainersPageAction'))
            ),
        array('@' => array(
                  'class' => 'DependenciesPage',
                  'id' => 'page4',
                  'name' => 'Dependencies'),
              'drop'   => array('@' => array('class' => 'DependenciesPageAction')),
              'add'    => array('@' => array('class' => 'DependenciesPageAction')),
              'edit'   => array('@' => array('class' => 'DependenciesPageAction')),
              'save'   => array('@' => array('class' => 'DependenciesPageAction')),
              'cancel' => array('@' => array('class' => 'DependenciesPageAction'))
            ),
        array('@' => array(
                  'class' => 'ReplacementsPage',
                  'id' => 'page5',
                  'name' => 'Replacements'),
              'list'   => array('@' => array('class' => 'ReplacementsPageAction')),
              'ignore' => array('@' => array('class' => 'ReplacementsPageAction')),
              'edit'   => array('@' => array('class' => 'ReplacementsPageAction')),
              'remove' => array('@' => array('class' => 'ReplacementsPageAction')),
              'new'    => array('@' => array('class' => 'ReplacementsPageAction')),
              'save'   => array('@' => array('class' => 'ReplacementsPageAction')),
              'cancel' => array('@' => array('class' => 'ReplacementsPageAction'))
            ),
        array('@' => array(
                  'class' => 'ExceptionsPage',
                  'id' => 'page7',
                  'name' => 'Exceptions'),
              'edit'   => array('@' => array('class' => 'ExceptionsPageAction')),
              'remove' => array('@' => array('class' => 'ExceptionsPageAction')),
              'save'   => array('@' => array('class' => 'ExceptionsPageAction')),
              'cancel' => array('@' => array('class' => 'ExceptionsPageAction'))
            ),
        array('@' => array(
                  'class' => 'PreviewPage',
                  'id' => 'page8',
                  'name' => 'Preview')
            ),
        array('@' => array(
                  'class' => 'ErrorsPage',
                  'id' => 'page0',
                  'name' => 'Errors')
            )
    );

    /**
     * All default wizard controller actions definition
     *
     * @var    array
     * @since  0.3.0
     * @access private
     */
    var $_actions = array(
        'display' => 'ActionDisplay',
        'process' => 'ActionProcess',
        'dump'    => false
    );

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

        // load all default preferences
        $config = false;
        $this->loadPreferences($config);

        // build a new non-modal controller
        $this->_qfc = new HTML_QuickForm_Controller($driver, false);

        // add all wizard default pages
        $this->addPages();

        // add all wizard default actions
        $this->addActions();
    }

    /**
     * Adds all pages of wizard at once
     *
     * @param  mixed $pages  Wizard pages definition array or null if used defaults
     * @return void
     * @access public
     * @since  0.1.0
     */
    function addPages($pages = null)
    {
         if (!isset($pages)) {
             // default wizard pages
             $pages = $this->_pages;
         }

         foreach($pages as $page) {
             $this->addPage($page);
         }
    }

    /**
     * Add a specific page to wizard or each page one by one
     *
     * @param  array $page  a single Wizard page definition
     * @return void
     * @access public
     * @since  0.1.0
     */
    function addPage($page)
    {
        $className = $page['@']['class'];
        $pageName  = $page['@']['name'];

        $qfcPage =& new $className($pageName);
        $this->_qfc->addPage($qfcPage);

        // adds additional action
        foreach ($page as $action => $attr) {
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
    function addActions($actions = null)
    {
        if (isset($actions) && is_array($actions)) {
            $this->_actions = array_merge($this->_actions, $actions);
        }

        // adds display driver
        $ActionDisplay = $this->_actions['display'];
        if (!class_exists($ActionDisplay)) {
            include_once 'PEAR/PackageFileManager/Frontend/Web/Default.php';
            $ActionDisplay = 'ActionDisplay';
        }
        $this->_qfc->addAction('display', new $ActionDisplay() );

        // adds basic actions (abort, commit, reset)
        $ActionProcess = $this->_actions['process'];
        if (!class_exists($ActionProcess)) {
            include_once 'PEAR/PackageFileManager/Frontend/Web/Process.php';
            $ActionProcess = 'ActionProcess';
        }
        $this->_qfc->addAction('abort',  new $ActionProcess() );
        $this->_qfc->addAction('commit', new $ActionProcess() );
        $this->_qfc->addAction('reset',  new $ActionProcess() );

        // adds dump class action (if necessary)
        $ActionDump = $this->_actions['dump'];
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
            $defaults = array();
            foreach($this->_pages as $page) {
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