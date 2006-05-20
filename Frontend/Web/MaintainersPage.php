<?php
/**
 * Creates the page for gathering information about developers.
 *
 * The add maintainer page needs to get the developer's name,
 * handle, email address and role.
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
 * Creates the page for gathering information about developers.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class MaintainersPage extends TabbedPage
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
        $this->addElement('header', null, 'Manage the list of maintainers');

        $fe =& PEAR_PackageFileManager_Frontend::singleton();
        $sess =& $fe->container();

        $selection = $this->getSubmitValue('users');
        $selection_count = count($selection);
        $fe->log('debug',
            str_pad($this->getAttribute('id') .'('. __LINE__ .')', 20, '.') .
            ' selection='. serialize($selection)
        );
        list($page, $action) = $this->controller->getActionName();

        // selection list (false) or edit dialog frame (true)
        if ($action == 'edit' && $selection_count == 1) {
            $editDialog = true;
        } elseif ($action == 'add' && $selection_count == 0) {
            $editDialog = true;
        }elseif ($action == 'save') {
            $editDialog = true;
        } else {
            $editDialog = false;
        }

        $leads = $fe->getMaintList('lead');
        if ($leads !== false) {
            $leads = true;
        }
        // at least a package lead is mandatory; used for form validation
        $this->addElement('hidden', 'leads', $leads);
        $this->setConstants($leads);
        $this->addRule('leads', 'You must specify a lead', 'nonzero');
        $maintainers = $fe->getMaintList();
        // set default maintainers list used when we click on 'Reset' button
        $fe->setDefaults('maintainers', $maintainers);

        if (!$editDialog) {

            $hdr = array('Handle', 'Name', 'Email', 'Role', 'Active');
            $table = new HTML_Table(array('class' => 'tableone'));
            $htmltableDecorator = new PEAR_PackageFileManager_Frontend_Decorator_HTMLTable($fe);
            $htmltableDecorator->setHtmlTable($table);
            $htmltableDecorator->getMaintList(null, $hdr);
            // We need a simple static html area for maintainers list.
            $this->addElement('static', 'maintainers', '', $htmltableDecorator->toHtml() );

            $commands = array('drop', 'edit', 'add');
            $nocmd    = array('commit');

        } else {

            // Role options list: (value => text, with value === text)
            $roles = $fe->getOption('maintainer_roles');
            sort($roles, SORT_ASC);
            $roles = array_combine($roles, $roles);

            // Active options list: (value => text, with value === text)
            $activ = array('yes', 'no');
            $activ = array_combine($activ, $activ);

            $this->addElement('text', 'handle', 'Handle:', array('size' => 20));
            $this->addElement('text', 'name'  , 'Name:'  , array('size' => 40));
            $this->addElement('text', 'email' , 'Email:' , array('size' => 40));
            $this->addElement('select', 'role', 'Role:', $roles);
            $this->addElement('select', 'active', 'Active:', $activ);

            if ($selection_count == 0) {
                $key1 = -1;
                $def = array('role' => 'lead', 'active' => 'yes');
            } else {
                $needle = array_keys($selection);
                $key1   = array_pop($needle);
                $def = array(
                    'handle' => $maintainers[$key1]['handle'],
                    'name'   => $maintainers[$key1]['name'],
                    'email'  => $maintainers[$key1]['email'],
                    'role'   => $maintainers[$key1]['role'],
                    'active' => $maintainers[$key1]['active']
                );
            }
            $this->addElement('hidden', 'userid', $key1);

            // applies new filters to the element values
            $this->applyFilter('__ALL__', 'trim');
            // form rules
            $this->addRule('handle','The handle of maintainer is required', 'required');
            $this->addRule('name',  'The name of maintainer is required'  , 'required');
            $this->addRule('email', 'The email of maintainer is required' , 'required');
            $this->addRule('email', 'Wrong email format'                  , 'email');

            // old values of edit user
            $this->setDefaults($def);

            $commands = array('save', 'cancel');
            $nocmd    = array('commit','reset');
        }

        // Buttons of the wizard to do the job
        $this->buildButtons($nocmd, $commands);
    }

    /**
     * Sets the default values for the maintainers page
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

        // apply only when in list mode,
        if ($action == 'reset') {
            $sess =& $fe->container();
            if (isset($sess['defaults']['_maintainers']) && $sess['defaults']['_maintainers']) {
                $maintainers = $fe->getMaintList();
                if ($maintainers) {
                    foreach($maintainers as $maintainer) {
                        $fe->deleteMaintainer($maintainer['handle']);
                    }
                }
                foreach($sess['defaults']['_maintainers'] as $maintainer) {
                    extract($maintainer);
                    $fe->addMaintainer($role, $handle, $name, $email, $active);
                }
            }
        }
    }
}

/**
 * Manage actions to add or edit information about developers.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @since      Class available since Release 0.1.0
 */
class MaintainersPageAction extends HTML_QuickForm_Action
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
                    $selection = $page->getSubmitValue('users');
                    if (is_array($selection)) {
                        $maintainers = $fe->getMaintList();
                        $keys = array_keys($selection);
                        foreach ($keys as $key1) {
                            $fe->log('info',
                                str_pad($pageName .'('. __LINE__ .')', 20, '.') .
                                ' drop maintainer: "'. $maintainers[$key1]['handle']
                                .'" ('. $maintainers[$key1]['role'] .')'
                            );
                            $fe->deleteMaintainer($maintainers[$key1]['handle']);
                        }
                    }
                    break;
                case 'save':
                    $data = $page->exportValues(array('handle','name','email','role','active'));
                    extract($data);
                    $key1 = $sess['values'][$pageName]['userid'];
                    if ($key1 < 0) {
                        $sess['pfm']->addMaintainer($role, $handle, $name, $email, $active);
                        $fe->log('info',
                             str_pad($pageName .'('. __LINE__ .')', 20, '.') .
                             ' add maintainer: "'. $data['handle'] .'" ('. $data['role'] .')'
                        );
                    } else {
                        $sess['pfm']->updateMaintainer($role, $handle, $name, $email, $active);
                        $fe->log('info',
                             str_pad($pageName .'('. __LINE__ .')', 20, '.') .
                             ' edit maintainer: "'. $data['handle'] .'" ('. $data['role'] .')'
                        );
                    }
                    break;
            }
            return $page->handle('jump');
        }
        return $page->handle('display');
    }
}
?>