<?php
/**
 * Creates the page for gathering information about roles.
 *
 * Roles influence both where a file is installed and how it is installed.
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

/**
 * Creates the page for gathering information about roles.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class RolesPage extends TabbedPage
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
        $this->addElement('header', null, 'Manage the list of roles');

        $fe = &PEAR_PackageFileManager_Frontend::singleton();
        $sess =& $fe->container();

        $roles = $fe->getOption('roles');

        $ext_roles = array('*' => $roles['*']);
        $dir_roles = array();

        $fe->log('debug',
            str_pad($this->getAttribute('id') .'('. __LINE__ .')', 20, '.') .
            ' ext roles='. serialize($ext_roles)
        );
        $fe->log('debug',
            str_pad($this->getAttribute('id') .'('. __LINE__ .')', 20, '.') .
            ' dir roles='. serialize($dir_roles)
        );

        $selection = $this->getSubmitValue('roles');
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

        // set default roles list used when we click on 'Reset' button
        $fe->setDefaults('roles');

        if (!$editDialog) {

            $hdr = array('Directory', 'Extension', 'Role');
            $table = new HTML_Table(array('class' => 'tableone'));
            $htmltableDecorator = new PEAR_PackageFileManager_Frontend_Decorator_HTMLTable($fe);
            $htmltableDecorator->setHtmlTable($table);
            $htmltableDecorator->getRoleList(false, $hdr);
            // We need a simple static html area for maintainers list.
            $this->addElement('static', 'roles', '', $htmltableDecorator->toHtml());

            $commands = array('drop', 'edit');
            $nocmd    = array('commit');

        } else {

            // Role options list: (value => text, with value === text)
            require_once 'PEAR/Installer/Role.php';
            $pageName = $fe->getPageName('page1');
            $releaseType = $fe->exportValue($pageName, 'packageType');
            $roles = PEAR_Installer_Role::getValidRoles($releaseType);
            sort($roles, SORT_ASC);
            $roles = array_combine($roles, $roles);

            if ($selection_count == 0) {
                $keys = $def = array();
                $directory = !is_null($this->getSubmitValue('directory'));
            } else {
                $keys = $needle = array_keys($selection);
                $key1 = array_shift($needle);
                $def = array(
                    'directory' => $sess['roles'][$key1]['directory'],
                    'extension' => $sess['roles'][$key1]['extension'],
                    'role'      => $sess['roles'][$key1]['role']
                );
                $directory = !empty($def['directory']);
            }

            $rolesid = array();
            foreach($keys as $k) {
                if (empty($sess['roles'][$k]['directory'])) {
                    $rolesid[$k] = $sess['roles'][$k]['extension'];
                } else {
                    $rolesid[$k] = $sess['roles'][$k]['directory'];
                }
            }

            // directory and extension are exclusive
            if ($directory) {
                $rDirectory =& $this->addElement('select', 'directory', 'Directory:');
                $rDirectory->load($rolesid, $keys);
                $rDirectory->setMultiple(true);
                $rDirectory->freeze();
            } else {
                $rExtension =& $this->addElement('select', 'extension', 'Extension:');
                $rExtension->load($rolesid, $keys);
                $rExtension->setMultiple(true);
                $rExtension->freeze();
            }

            $this->addElement('select', 'role', 'Role:', $roles);

            // applies new filters to the element values
            $this->applyFilter('__ALL__', 'trim');
            // form rules
            $this->addRule('role', 'The role of extension is required' , 'required');

            // old values of edit user
            $this->setDefaults($def);

            $commands = array('save', 'cancel');
            $nocmd    = array('commit','reset');
        }

        // Buttons of the wizard to do the job
        $this->buildButtons($nocmd, $commands);
    }

    /**
     * Sets the default values for the roles page
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

        if ($action == 'reset') {
            $fe->getRoleList(true);
        }
    }
}

/**
 * Manage actions to remove or edit a role mapping for directory or extension.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @since      Class available since Release 0.1.0
 */
class RolesPageAction extends HTML_QuickForm_Action
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

        if ($actionName == 'add' || $actionName == 'edit' || $actionName == 'cancel') {
            return $page->handle('display');
        }

        // save the form values and validation status to the session
        $sess =& $fe->container();
        $sess['values'][$pageName] = $page->exportValues();
        $sess['valid'][$pageName]  = $page->validate();

        if (isset($sess['valid'][$pageName]) && $sess['valid'][$pageName]) {

            switch ($actionName) {
                case 'drop':
                    $selection = $page->getSubmitValue('roles');
                    if (is_array($selection)) {
                        $keys = array_keys($selection);
                        foreach ($keys as $k) {
                            if (empty($sess['roles'][$k]['directory'])) {
                                $_for = $sess['roles'][$k]['extension'] .'" (extension)';
                            } else {
                                $_for = $sess['roles'][$k]['directory'] .'" (directory)';
                            }
                            $fe->log('info',
                                str_pad($pageName .'('. __LINE__ .')', 20, '.') .
                                ' drop role: "'. $sess['roles'][$k]['role'] .'" for "'. $_for
                            );
                            unset($sess['roles'][$k]);
                        }
                    }
                    break;
                case 'save':
                    $data = $page->exportValues();
                    $keys = isset($data['directory']) ? $data['directory'] : $data['extension'];
                    if (!is_array($keys)) {
                        $keys = array($keys);
                    }
                    foreach($keys as $k) {
                        if (empty($sess['roles'][$k]['directory'])) {
                            $_for = $sess['roles'][$k]['extension'] .'" (extension)';
                        } else {
                            $_for = $sess['roles'][$k]['directory'] .'" (directory)';
                        }
                        $fe->log('debug',
                            str_pad($pageName .'('. __LINE__ .')', 20, '.') .
                            ' edit role for: "'. $_for
                        );
                        $sess['roles'][$k]['role'] = $data['role'];
                    }
                    break;
            }
            return $page->handle('jump');
        }
        return $page->handle('display');
    }
}
?>