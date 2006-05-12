<?php
/**
 * Creates the page for displaying main options
 * that relate to the entire package, regardless of the release.
 *
 * Package options are those such as: base install directory,
 * license, package name, etc...
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
 * Creates the page for displaying main options
 * that relate to the entire package, regardless of the release.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class PackagePage extends TabbedPage
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
        $this->addElement('header', null, 'Package Summary');

        // We need a simple entry box for the channel selection.
        $this->addElement('text', 'channel',
                          array('Channel :', 'Default download source'),
                          array('size' => 50)
        );

        // We need a group entry box for the PEAR installer version.
        $installer['min']         =& $this->createElement('text', 'min', 'min', array('size' => 12));
        $installer['max']         =& $this->createElement('text', 'max', 'max', array('size' => 12));
        $installer['recommended'] =& $this->createElement('text', 'recommended', 'recommended', array('size' => 12));
        $installer['exclude']     =& $this->createElement('text', 'exclude', 'exclude', array('size' => 12));
        $this->addGroup($installer, 'pearInstaller', 'PEAR installer :', '');

        // We need a group entry box for the PHP version.
        $php['min']     =& $this->createElement('text', 'min', 'min', array('size' => 12));
        $php['max']     =& $this->createElement('text', 'max', 'max', array('size' => 12));
        $php['exclude'] =& $this->createElement('text', 'exclude', 'exclude', array('size' => 12));
        $this->addGroup($php, 'phpVersion', 'PHP version :', '');

        // Package type options list: (value => text, with value === text)
        $fe =& PEAR_PackageFileManager_Frontend::singleton();
        $settings = $fe->getOption(array('settings', 'pfm'), false);
        $packagetype = $settings['pfm']['package_type'];
        sort($packagetype, SORT_ASC);
        $packagetype = array_combine($packagetype, $packagetype);

        // We need a simple combo box for the package type selection.
        $this->addElement('select', 'packageType',
                          array('Package Type :', 'Specify the content type of a release'),
                          $packagetype
        );

        // We need a simple entry box for the package directory selection.
        $this->addElement('text', 'packageDir',
                          array('Package File Directory :', 'The path to the base directory of the package'),
                          array('size' => 100)
        );

        // We need a simple entry box for the package output directory.
        $this->addElement('text', 'packageOutputDir',
                          array('Package Output Directory :', 'The path in which to place the generated package.xml'),
                          array('size' => 100)
        );

        // We need a simple entry box for the package filename.
        $this->addElement('text', 'packageFileName',
                          array('Package FileName :', 'The name of the packagefile, defaults to package.xml'),
                          array('size' => 50)
        );

        // We need a simple entry box for the package name.
        $this->addElement('text', 'packageName',
                          array('Package Name :', 'Use this to create a new package.xml, or overwrite an existing one'),
                          array('size' => 50)
        );

        // We need a simple entry box for the base install directory.
        $this->addElement('text', 'baseInstallDir',
                          array('Base Install Dir :', 'The base directory to install the package in'),
                          array('size' => 50)
        );

        // We need a simple entry box for the package summary.
        $this->addElement('text', 'packageSummary',
                          array('Package Summary :', 'Summary of package purpose'),
                          array('size' => 100)
        );

        // We need a text area for the package description.
        $this->addElement('textarea', 'packageDescription',
                          array('Package Description :', 'Description of package purpose'),
                          array('rows' => 4, 'cols' => 74)
        );

        // Validation form rules
        $installerRules['min'][0] = array('Minimum version is required', 'required');
        $this->addGroupRule('pearInstaller', $installerRules);

        $phpRules['min'][0] = array('Minimum version is required', 'required');
        $this->addGroupRule('phpVersion', $phpRules);

        $this->addRule('packageType', 'The package type is required'                               , 'required');
        $this->addRule('packageDir', 'The path to the base directory of the package is required'   , 'required');
        $this->addRule('packageName', 'The package name is required'                               , 'required');
        $this->addRule('baseInstallDir', 'The base directory to install the package in is required', 'required');
        $this->addRule('packageSummary', 'Summary of package purpose is required'                  , 'required');
        $this->addRule('packageDescription', 'Description of package is required'                  , 'required');

        // Buttons of the wizard to do the job
        $this->buildButtons(array('commit'));
    }
}
?>