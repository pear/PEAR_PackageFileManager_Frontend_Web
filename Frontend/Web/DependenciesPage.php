<?php
/**
 * Creates the page to add dependency on another package or php extension.
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
 * Creates the page to add dependency on another package or php extension.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class DependenciesPage extends TabbedPage
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
        $this->addElement('header', null, 'Manage the list of dependencies');

        $fe =& PEAR_PackageFileManager_Frontend::singleton();
        $sess =& $fe->container();

        $selection = $this->getSubmitValue('deps');
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

        $dependencies = $fe->getDepList();
        // set default dependencies list used when we click on 'Reset' button
        $fe->setDefaults('dependencies');

        if (!$editDialog) {

            $hdr = array('Type', 'Channel', 'Name', 'Extension', 'Min', 'Max', 'Recommended', 'Exclude');
            $table = new HTML_Table(array('class' => 'tableone'));
            $htmltableDecorator = new PEAR_PackageFileManager_Frontend_Decorator_HTMLTable($fe);
            $htmltableDecorator->setHtmlTable($table);
            $htmltableDecorator->getDepList($hdr);
            // We need a simple static html area for maintainers list.
            $this->addElement('static', 'dependencies', '', $htmltableDecorator->toHtml());

            $commands = array('drop', 'edit', 'add');
            $nocmd    = array('commit', 'reset');

        } else {

            $channels = array_keys($sess['packages']);
            array_unshift($channels, '');
            $channels = array_combine($channels, $channels);

            $names[''] = array();
            foreach($sess['packages'] as $c => $p) {
                if (count($p)) {
                    $names[$c] = array_combine($p, $p);
                } else {
                    $names[$c] = array();
                }
            }

            $extensions = $sess['extensions'];
            $extensions[] = '';
            sort($extensions, SORT_ASC);
            $extensions = array_combine($extensions, $extensions);

            // Type options list: (value => text, with value === text)
            $types = array('group-package', 'group-subpackage', 'group-extension', 'optional', 'required');
            $types = array_combine($types, $types);

            $this->addElement('select', 'type', 'Type:', $types, array('style' => 'width:10em;'));

            $group = array();
            $group[] = &HTML_QuickForm::createElement('text', 'groupname', 'Name', array('size' => 10));
            $group[] = &HTML_QuickForm::createElement('text', 'grouphint', 'Hint', array('size' => 70));
            $this->addGroup($group, 'group', 'Group :', '', false);

            $this->addElement('select', 'extension', 'Extension:', $extensions, array('style' => 'width:10em;'));

            $pkgInstalled =& $this->addElement('hierselect', 'name', null, array('class' => 'flat'), '&nbsp;');
            $pkgInstalled->setLabel('Name:');
            $pkgInstalled->setOptions(array($channels, $names));

            $this->addElement('text', 'min', 'Min:', array('size' => 10));
            $this->addElement('text', 'max', 'Max:', array('size' => 10));
            $this->addElement('text', 'recommended', 'Recommended:', array('size' => 10));
            $this->addElement('text', 'exclude', 'Exclude:', array('size' => 10));

            if ($selection_count == 0) {
                $key1 = -1;
                $def = array('type' => 'required');
            } else {
                $needle = array_keys($selection);
                $key1   = array_pop($needle);
                $def = array(
                    'type'        => $dependencies[$key1]['type'],
                    'groupname'   => $dependencies[$key1]['group'] ? $dependencies[$key1]['group']['name'] : '',
                    'grouphint'   => $dependencies[$key1]['group'] ? $dependencies[$key1]['group']['hint'] : '',
                    'extension'   => $dependencies[$key1]['extension'],
                    'name'        => array($dependencies[$key1]['channel'], $dependencies[$key1]['name']),
                    'min'         => $dependencies[$key1]['min'],
                    'max'         => $dependencies[$key1]['max'],
                    'recommended' => $dependencies[$key1]['recommended'],
                    'exclude'     => $dependencies[$key1]['exclude']
                );
            }
            $this->addElement('hidden', 'depid', $key1);

            // applies new filters to the element values
            $this->applyFilter('__ALL__', 'trim');
            // apply form rules
            $this->addFormRule(array(&$this, 'cbValidateDependency'));

            // old values to edit
            $this->setDefaults($def);

            $commands = array('save', 'cancel');
            $nocmd    = array('commit','reset');
        }

        // Buttons of the wizard to do the job
        $this->buildButtons($nocmd, $commands);
    }

    /**
     * Callback to validate Dependency form page in edit mode
     *
     * @return TRUE on form validation success, errors array if form validation failed
     * @since  0.1.0
     * @access private
     */
    function cbValidateDependency($fields)
    {
        $errors = array();

        if (substr($fields['type'], 0, 5) == 'group') {
            if (empty($fields['groupname'])) {
                $errors['group'] = 'Group name is required';
            } elseif (empty($fields['grouphint'])) {
                $errors['group'] = 'Group hint is required';
            } else {
                if ($fields['type'] == 'group-extension') {
                    if (empty($fields['extension'])) {
                        $errors['extension'] = 'Extension is required';
                    }
                } else {
                    if (count($fields['name']) == 1) {
                        $errors['name'] = 'Package is required';
                    }
                }
            }
        } else {
            if (empty($fields['extension']) && count($fields['name']) == 1) {
                $errors['name'] = 'Extension or Package is required';
            }
        }
        return empty($errors)? true: $errors;
    }

    /**
     * Sets the default values for the dependencies page
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
 * Manage actions to add or edit dependency on another package or php extension.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @since      Class available since Release 0.1.0
 */
class DependenciesPageAction extends HTML_QuickForm_Action
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
                    $selection = $page->getSubmitValue('deps');
                    if (is_array($selection)) {
                        $keys = array_keys($selection);
                        foreach ($keys as $key1) {
                            if (empty($sess['dependencies'][$key1]['extension'])) {
                                $_dep = $sess['dependencies'][$key1]['name'] .'" (package)';
                            } else {
                                $_dep = $sess['dependencies'][$key1]['extension'] .'" (extension)';
                            }
                            $fe->log('info',
                                str_pad($pageName .'('. __LINE__ .')', 20, '.') .
                                ' drop dependency: "'. $_dep
                            );
                            unset($sess['dependencies'][$key1]);
                        }
                    }
                    break;
                case 'save':
                    $data = $page->exportValues(array(
                        'type', 'group', 'extension', 'name',
                        'min', 'max', 'recommended', 'exclude'
                        ));
                    $data['channel'] = $data['name'][0];
                    $data['name'] = $data['name'][1];
                    if (substr($data['type'], 0, 5) == 'group') {
                        $data['group'] = array(
                            'name' => $data['group']['groupname'],
                            'hint' => $data['group']['grouphint']
                            );
                    } else {
                        $data['group'] = false;
                    }
                    unset($data['group']['groupname'], $data['group']['grouphint']);
                    $key1 = $sess['values'][$pageName]['depid'];
                    if (empty($data['extension'])) {
                        $_dep = $data['name'] .'" (package)';
                        unset($data['extension']);
                    } else {
                        $_dep = $data['extension'] .'" (extension)';
                        unset($data['name']);
                    }
                    if (empty($data['min'])) {
                        unset($data['min']);
                    }
                    if (empty($data['max'])) {
                        unset($data['max']);
                    }
                    if (empty($data['recommended'])) {
                        unset($data['recommended']);
                    }
                    if (empty($data['exclude'])) {
                        unset($data['exclude']);
                    }
                    if ($key1 < 0) {
                        array_push($sess['dependencies'], $data);
                        $fe->log('info',
                            str_pad($pageName .'('. __LINE__ .')', 20, '.') .
                            ' add dependency: "'. $_dep
                        );
                    } else {
                        $sess['dependencies'][$key1] = $data;
                        $fe->log('info',
                            str_pad($pageName .'('. __LINE__ .')', 20, '.') .
                            ' edit dependency: "'. $_dep
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