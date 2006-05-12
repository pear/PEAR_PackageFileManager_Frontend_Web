<?php
/**
 * Interactive memory debugging tool.
 *
 * You can display contents of :
 * - default PEAR_PackageFileManager2 class options
 * - this frontend options
 * - all forms values, defaults and validation states
 * - the Warnings/Errors stack
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    CVS: $Id$
 * @since      File available since Release 0.1.0
 */

require_once 'HTML/QuickForm/Action.php';

function varDump($var)
{
    $available = PEAR_PackageFileManager2::isIncludeable('Var_Dump.php');
    if ($available) {
        include_once 'Var_Dump.php';
        Var_Dump::display($var, false, array('display_mode' => 'HTML4_Table'));
    } else {
        $styles = array('');
        echo '<pre style="background-color:#eee; color:#000; padding:1em;">';
        var_dump($var);
        echo '</pre>';
    }
}

/**
 * You can display contents of :
 * - default PEAR_PackageFileManager2 class options
 * - this frontend options
 * - all forms values, defaults and validation states
 * - the Warnings/Errors stack
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class ActionDump extends HTML_QuickForm_Action
{
    /**
     * Processes the request.
     *
     * @param  object   HTML_QuickForm_Page  the current form-page
     * @param  string   Current action name, as one Action object can serve multiple actions
     * @since  0.1.0
     * @access public
     */
    function perform(&$page, $actionName)
    {
        $fe = &PEAR_PackageFileManager_Frontend::singleton();
        $fe->log('debug',
            str_pad($page->getAttribute('id') .'('. __LINE__ .')', 20, '.') .
            ' ActionProcess='. $actionName
        );

        $page->isFormBuilt() or $page->buildForm();
        $page->handle('display');

        $sess =& $fe->container();

        $opt = $page->getSubmitValue('dumpOption');
        switch ($opt) {
            case '0':   // PFM options
                break;
            case '1':   // GUI options
                $settings = $fe->getOption(array('settings'), false);
                varDump($settings);
                break;
            case '2':   // Forms values container
                $data = $fe->container();
                unset($data['pfm']);
                varDump($data);
                break;
            case '3':   // Errors/Warnings stack
                $errors = PEAR_PackageFileManager_Frontend::getErrors();
                varDump($errors);
                break;
            case '4':   // Included files
                $includes = get_included_files();
                varDump($includes);
                break;
            case '5':   // PFM package info
                varDump($sess['pfm']->getArray());
                break;
            case '6':   // declared classes
                $classes = get_declared_classes();
                varDump($classes);
                break;
        }
    }
}
?>