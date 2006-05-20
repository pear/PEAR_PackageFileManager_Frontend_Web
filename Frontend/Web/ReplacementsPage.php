<?php
/**
 * Creates the page to add simple or global replacements.
 *
 * Replacements are install time search and replace strings
 * that can be used to set certain package variables to
 * values found on the user's system or that are specific
 * to the version of the installed package.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    CVS: $Id$
 * @since      File available since Release 0.1.0
 */

require_once 'Pager/Pager.php';

/**
 * Creates the page to add simple or global replacements.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class ReplacementsPage extends TabbedPage
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
        $this->addElement('header', null, 'Add a replacement option for one or all files');

        $fe =& PEAR_PackageFileManager_Frontend::singleton();
        $sess =& $fe->container();

        $selection = $this->getSubmitValue('files');
        if (isset($selection) && !is_array($selection)) {
            $selection = array($selection);
        }

        $selection_count = empty($selection) ? 0 : count($selection);
        $fe->log('debug',
            str_pad($this->getAttribute('id') .'('. __LINE__ .')', 20, '.') .
            ' selection='. serialize($selection)
        );

        list($page, $action) = $this->controller->getActionName();

        // selection list (false) or edit dialog frame (true)
        if ($action == 'edit' && $selection_count > 0) {
            $editDialog = true;
        }elseif ($action == 'save' || $action == 'new') {
            $editDialog = true;
        } else {
            $editDialog = false;
        }

        // set default dependencies list used when we click on 'Reset' button
        $fe->setDefaults('files');

        if (!$editDialog) {

            // File list Generator options list: (value => text, with value === text)
            $generator = $fe->getOption('plugingenerator');
            sort($generator, SORT_ASC);
            $generator = array_combine($generator, $generator);

            // We need a group combo box + button for the file list plugin generator.
            $plugin = array();
            $plugin[] = &HTML_QuickForm::createElement('select', 'filelistgenerator', 'plugin', $generator);
            $plugin[] = &HTML_QuickForm::createElement('submit', $this->getButtonName('list'), 'List');
            $this->addGroup($plugin, 'plugin', 'File list generator :', '', false);

            $flplugin = $this->getSubmitValue('filelistgenerator');

            $hdr = array('Path', 'Replaces');
            $table = new HTML_Table(array('class' => 'tableone'));
            $htmltableDecorator = new PEAR_PackageFileManager_Frontend_Decorator_HTMLTable($fe);
            $htmltableDecorator->setHtmlTable($table);
            $htmltableDecorator->getFileList(false, false, $flplugin, $hdr);
            // We need a simple static html area for package files list.
            $this->addElement('static', 'packagefiles', '', $htmltableDecorator->toHtml());

            $def = array('filelistgenerator' => $fe->getOption('filelistgenerator'));
            $this->setDefaults($def);

            $commands = array('ignore', 'edit', 'remove');
            $nocmd    = array('commit');

        } else {
            $types = array('', 'package-info','pear-config','php-const');

            $const = get_defined_constants();
            $const = array_keys($const);
            sort($const, SORT_ASC);

            $pearConfig =& new PEAR_Config();
            $keys = $pearConfig->getKeys();
            sort($keys, SORT_ASC);

            $package_info = array('name', 'summary', 'channel', 'notes',
                'extends', 'description', 'release_notes', 'license',
                'release-license', 'license-uri', 'version', 'api-version',
                'state', 'api-state', 'release_date', 'date', 'time'
            );

            // for each "replace type"
            for ($i = 0; $i < 4; $i++) {
                $select_type[$types[$i]] = $types[$i];
                // retrieve associative "replace to" values
                switch ($i) {
                    case 0: // none
                        $select_to[$types[$i]][''] = '';
                        break;
                    case 1: // from package-info
                        foreach($package_info as $section) {
                            $select_to[$types[$i]][$section] = $section;
                        }
                        break;
                    case 2: // from pear-config
                        foreach($keys as $config) {
                            $select_to[$types[$i]][$config] = $config;
                        }
                        break;
                    case 3: // from php-const
                        foreach($const as $j => $constName) {
                            $select_to[$types[$i]][$constName] = $constName;
                        }
                        break;
                }
            }

            // we need a simple text box for the string to replace
            $this->addElement('text', 'replace_from', 'Replace:', array('size' => 40));

            // we need a multiple-select box for list of file targets
            $rPath =& $this->addElement('select', 'replace_file');
            $rPath->setMultiple(true);
            $label  = 'Into file';
            $label .= ($selection_count > 1) ? 's' : '';
            $label .= ':';
            $rPath->setLabel($label);
            $rPath->freeze();

            $replace_type =& $this->addElement('hierselect', 'replace_type', null,
                array('class' => 'flat'), '<br />');
            $replace_type->setLabel('Type:');
            $replace_type->setOptions(array($select_type, $select_to));

            // we need a simple select box for platform exception list
            $platform = array('', 'windows', '(*ix|*ux)');
            $platform = array_combine($platform, $platform);
            $this->addElement('select', 'platform_exception', 'Platform exception:', $platform);

            if ($selection_count == 0) {
                $key1 = -1;
                $def = array();
                $rPath->load($sess['files']['mapping']);
            } else {
                $keys = $needle = array_keys($selection);
                $key1 = array_shift($needle);

                $params = array(
                    'httpMethod'  => 'POST',
                    'clearIfVoid' => false,
                    'perPage'     => 1,
                    'itemData'    => $sess['files'][$key1]['replacements']
                );
                $pager =& Pager::factory($params);
                $pgid  = $pager->getCurrentPageID();
                $item  = $pager->getPageData();
                $fe->log('debug',
                    str_pad($this->getAttribute('id') .'('. __LINE__ .')', 20, '.') .
                    ' page id.='. $pgid
                );
                $fe->log('debug',
                    str_pad($this->getAttribute('id') .'('. __LINE__ .')', 20, '.') .
                    ' item id.='. serialize($item)
                );
                $fe->log('debug',
                    str_pad($this->getAttribute('id') .'('. __LINE__ .')', 20, '.') .
                    ' item ct.='. $pager->numItems()
                );
                $links = $pager->getLinks();

                if ($item) {
                    $item = array_shift($item);
                    // We need a simple static html area for paging between replace items.
                    $this->addElement('static', 'paging', 'Item #:', $links['pages']);
                }

                $files = array();
                foreach($keys as $k) {
                    $files[$k] = $sess['files']['mapping'][$k];
                }
                $rPath->load($files, $keys);
                $def = array(
                    'replace_file' => $keys,
                    'replace_from' => $item['from'],
                    'replace_type' => array($item['type'], $item['to']),
                    'platform_exception' => $sess['files'][$key1]['platform']
                );
            }

            $this->addElement('hidden', 'replaceid', $pgid - 1);

            // old values of edit user
            $this->setDefaults($def);

            $commands = array('new', 'save', 'cancel');
            $nocmd    = array('commit','reset');
        }
        // applies new filters to the element values
        $this->applyFilter('__ALL__', 'trim');
        // apply form rules
        $this->addFormRule(array(&$this, 'cbValidateReplacement'));

        // Buttons of the wizard to do the job
        $this->buildButtons($nocmd, $commands);
    }

    /**
     * Callback to validate Replacement form page in edit mode
     *
     * @return TRUE on form validation success, errors array if form validation failed
     * @since  0.1.0
     * @access private
     */
    function cbValidateReplacement($fields)
    {
        $errors = array();
        if (empty($fields['replace_from']) && intval($fields['replace_type'][1]) > 0) {
            $errors['replace_from'] = 'String to replace is required';
        }
        return empty($errors)? true: $errors;
    }

    /**
     * Sets the default values for the replacements page
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
            $fe->getFileList(true);
        }
    }
}

/**
 * Manage actions to ignore file from selection or edit simple/global replacements.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @since      Class available since Release 0.1.0
 */
class ReplacementsPageAction extends HTML_QuickForm_Action
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
                case 'list':
                    unset($sess['files']);
                    $fe->getFilelist(false, false, $sess['values'][$pageName]['filelistgenerator']);
                    $fe->setDefaults('files', null, true);
                    break;
                case 'ignore':
                    $selection = $page->getSubmitValue('files');
                    if (is_array($selection)) {
                        $keys = array_keys($selection);
                        foreach ($keys as $k) {
                            $fe->log('info',
                                str_pad($pageName .'('. __LINE__ .')', 20, '.') .
                                ' ignore file: "' . $sess['files']['mapping'][$k] . '"'
                            );
                            $sess['files'][$k]['ignore'] = true;
                        }
                    }
                    break;
                case 'remove':
                    $selection = $page->getSubmitValue('files');
                    if (is_array($selection)) {
                        $keys = array_keys($selection);
                        foreach ($keys as $k) {
                            $sess['files'][$k]['replacements'] = array();
                        }
                    }
                    break;
                case 'new':
                case 'save':
                    $data = $page->exportValues(array(
                        'replace_file','replace_from','replace_type','platform_exception')
                    );

                    $keys = $data['replace_file'];
                    $fe->log('info',
                        str_pad($pageName .'('. __LINE__ .')', 20, '.') .
                        ' replace files: '. serialize($keys)
                    );
                    if (!is_array($keys)) {
                        $keys = array($keys);
                    }
                    foreach($keys as $k) {
                        if ($actionName == 'new') {
                            $rid = count($sess['files'][$k]['replacements']);
                        } else {
                            $rid = $sess['values'][$pageName]['replaceid'];
                        }
                        $sess['files'][$k]['replacements'][$rid] = array(
                            'from' => $data['replace_from'],
                            'type' => $data['replace_type'][0],
                            'to'   => $data['replace_type'][1]
                        );
                        $sess['files'][$k]['platform'] = empty($data['platform_exception'])
                            ? false : $data['platform_exception'];
                        $fe->log('info',
                            str_pad($pageName .'('. __LINE__ .')', 20, '.') .
                            ' add replacement: "'. $types[$data['replace_type'][0]] .
                            '" (from='. $data['replace_from'] .', to='. $data['to'] .
                            ') for "'. $data['mapping'] .'"'
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