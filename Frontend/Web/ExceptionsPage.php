<?php
/**
 * Creates the page to specify file role for specific files.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    CVS: $Id$
 * @since      File available since Release 0.1.0
 */

require_once 'PEAR/PackageFileManager/Frontend/Decorator/HTMLTable.php';
require_once 'PEAR/PackageFileManager/Frontend/Decorator/Filter.php';
require_once 'PEAR/Installer/Role.php';

/**
 * Creates the page to specify file role for specific files.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class ExceptionsPage extends TabbedPage
{
    /**
     * Builds the current form-page.
     *
     * @since  0.1.0
     * @access public
     */
    function buildForm()
    {
        $this->buildTabs();
        // tab caption
        $this->addElement('header', null, 'Specify file role for specific files');

        $fe =& PEAR_PackageFileManager_Frontend::singleton();
        $sess =& $fe->container();

        $selection = $this->getSubmitValue('files');
        $selection_count = count($selection);
        $fe->log('debug',
            str_pad($this->getAttribute('id') .'('. __LINE__ .')', 20, '.') .
            ' selection='. serialize($selection)
        );

        list($page, $action) = $this->controller->getActionName();

        // selection list (false) or edit dialog frame (true)
        if ($action == 'edit' && $selection_count > 0) {
            $editDialog = true;
        }elseif ($action == 'save') {
            $editDialog = true;
        } else {
            $editDialog = false;
        }

        if (!$editDialog) {

            foreach ($sess['defaults']['_files']['mapping'] as $fn) {
                $pinfo = pathinfo($fn);
                $ext[] = $pinfo['extension'];
            }
            $extensions = array_unique($ext);
            $extensions[] = '-None-';
            sort($extensions, SORT_ASC);
            $extensions = array_combine($extensions, $extensions);

            // Role options list: (value => text, with value === text)
            $pageName = $fe->getPageName('page1');
            $releaseType = $fe->exportValue($pageName, 'packageType');
            $roles = PEAR_Installer_Role::getValidRoles($releaseType);
            $roles[] = '-None-';
            sort($roles, SORT_ASC);
            $roles = array_combine($roles, $roles);

            $filters = array();
            $filters[] = &HTML_QuickForm::createElement('select', 'extensionFilter', 'Extension', $extensions);
            $filters[] = &HTML_QuickForm::createElement('select', 'roleFilter', 'Role', $roles);
            $filters[] = &HTML_QuickForm::createElement('submit', $this->getButtonName('sort'), 'Apply');
            $this->addGroup($filters, 'filters', 'Filters applied on list :', '', false);

            $hdr = array('Path', 'Role');
            $table = new HTML_Table(array('class' => 'tableone'));
            $htmltableDecorator = new PEAR_PackageFileManager_Frontend_Decorator_HTMLTable($fe);
            $htmltableDecorator->setHtmlTable($table);
            $htmltableDecorator->getExceptionList($hdr);
            // We need a simple static html area for maintainers list.
            $this->addElement('static', 'exceptions', '', $htmltableDecorator->toHtml());

            $def = array('extensionFilter' => '-None-', 'roleFilter' => '-None-');
            $this->setDefaults($def);

            $commands = array('edit', 'remove');
            $nocmd    = array('commit', 'reset');

        } else {

            // we need a multiple-select box for list of file targets
            $rPath =& $this->addElement('select', 'exceptfiles');
            $rPath->setMultiple(true);
            $rPath->setLabel('Path:');
            $rPath->freeze();

            // Role options list: (value => text, with value === text)
            $pageName = $fe->getPageName('page1');
            $releaseType = $fe->exportValue($pageName, 'packageType');
            $roles = PEAR_Installer_Role::getValidRoles($releaseType);
            $roles[] = '';
            sort($roles, SORT_ASC);
            $roles = array_combine($roles, $roles);
            $this->addElement('select', 'role', 'Role:', $roles);

            if ($selection_count == 0) {
                $key1 = -1;
                $def = array();
            } else {
                $keys = $needle = array_keys($selection);
                $key1 = array_shift($needle);

                $files = array();
                foreach($keys as $k) {
                    $files[$k] = $sess['files']['mapping'][$k];
                }
                $rPath->load($files, $keys);
                $def = array('exceptfiles' => $keys);
            }

            // applies new filters to the element values
            $this->applyFilter('__ALL__', 'trim');

            // old values of edit user
            $this->setDefaults($def);

            $commands = array('save', 'cancel');
            $nocmd    = array('commit','reset');
        }

        // Buttons of the wizard to do the job
        $this->buildButtons($nocmd, $commands);
    }

    /**
     * Sets the default values for the exceptions page
     *
     * @return void
     * @since  0.1.0
     * @access public
     * @see    HTML_QuickForm_Controller::applyDefaults()
     */
    function applyDefaults()
    {
        list($page, $action) = $this->controller->getActionName();
        $fe =& PEAR_PackageFileManager_Frontend::singleton();
        $fe->log('debug',
            str_pad($this->getAttribute('id') .'('. __LINE__ .')', 20, '.') .
            " applyDefaults ActionName=($page,$action)"
        );
    }
}

/**
 * Manage actions to add exception on roles mapping for specific files.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @since      Class available since Release 0.1.0
 */
class ExceptionsPageAction extends HTML_QuickForm_Action
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
        $page->isFormBuilt() or $page->buildForm();
        $pageName = $page->getAttribute('id');
        $fe =& PEAR_PackageFileManager_Frontend::singleton();
        $fe->log('debug',
            str_pad($pageName .'('. __LINE__ .')', 20, '.') .
            ' ActionProcess='. $actionName
        );

        if ($actionName == 'edit' || $actionName == 'cancel') {
            return $page->handle('display');
        }

        // save the form values and validation status to the session
        $sess =& $fe->container();
        $sess['values'][$pageName] = $page->exportValues();
        $sess['valid'][$pageName]  = $page->validate();

        if (isset($sess['valid'][$pageName]) && $sess['valid'][$pageName]) {

            switch ($actionName) {
                case 'sort':
                    $filters = array();
                    $filter1 = $sess['values'][$pageName]['extensionFilter'];
                    $filter2 = $sess['values'][$pageName]['roleFilter'];
                    if ($filter1 != '-None-') {
                        $filters['extension'] = $filter1;
                    }
                    if ($filter2 != '-None-') {
                        $filters['role'] = $filter2;
                    }

                    $filterDecorator = new PEAR_PackageFileManager_Frontend_Decorator_Filter($fe);
                    $filterDecorator->setFilters($filters);
                    $sess['files'] = $filterDecorator->getFileList();
                    break;
                case 'save':
                    $data = $page->exportValues(array('exceptfiles', 'role'));
                    $keys = $data['exceptfiles'];
                    $fe->log('info',
                        str_pad($pageName .'('. __LINE__ .')', 20, '.') .
                        ' exceptions on files: '. serialize($keys)
                    );
                    if (!is_array($keys)) {
                        $keys = array($keys);
                    }

                    foreach($keys as $key1) {
                        $sess['files'][$key1]['role'] = $data['role'];
                        $sess['defaults']['_files'][$key1]['role'] = $data['role'];
                        $fe->log('info',
                            str_pad($pageName .'('. __LINE__ .')', 20, '.') .
                            ' add exception: "'. $data['role'] .'" for "'. $sess['files'][$key1]['path'] .'"'
                        );
                    }
                    break;
                case 'remove':
                    $selection = $page->getSubmitValue('files');
                    if (is_array($selection)) {
                        $keys = array_keys($selection);
                        foreach ($keys as $k) {
                            $sess['files'][$k]['role'] = '';
                        }
                    }
                    break;
            }
            return $page->handle('jump');
        }
        return $page->handle('display');
    }
}
?>