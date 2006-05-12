<?php
/**
 * Creates the page for displaying the package new release options.
 *
 * The release options consist of features including, the release date,
 * the package version and state, and the release notes and license.
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
 * Creates the page for displaying the package new release options.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class ReleasePage extends TabbedPage
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
        $this->addElement('header', null, 'Release Informations ');

        $fe =& PEAR_PackageFileManager_Frontend::singleton();

        // State options list: (value => text, with value === text)
        $settings = $fe->getOption(array('settings', 'pfm'), false);
        $states = $settings['pfm']['stability'];
        sort($states, SORT_ASC);
        $states = array_combine($states, $states);

        // We need a combo box for the release state list.
        $this->addElement('select', 'releaseState', 'State :', $states);

        // We need a simple entry box for the release version.
        $this->addElement('text', 'releaseVersion',
                          array('Version :', 'The version number for this release'),
                          array('size' => 30)
        );

        // We need a combo box for the api state list.
        $this->addElement('select', 'APIState', 'API State :', $states);

        // We need a simple entry box for the api version.
        $this->addElement('text', 'APIVersion',
                          array('API Version :', 'The version number of current API'),
                          array('size' => 30)
        );

        // We need a date field for the release date
        $rDate =& $this->addElement('date', 'releaseDate',
                          array('Date : ', 'Publication date of the new release'),
                          array('format' => 'F d Y', 'language' => 'en')
        );
        $rDate->freeze();

        // We need a group entry box for the release license.
        $license['content'] =& $this->createElement('text', 'content', 'content', array('size' => 48));
        $license['uri']     =& $this->createElement('text', 'uri'    , 'uri'    , array('size' => 48));
        $this->addGroup($license, 'releaseLicense', 'License :', '');

        // We need a text area for the release notes.
        $this->addElement('textarea', 'releaseNotes',
                          array('Notes :', 'Release notes, any text describing what makes this release unique'),
                          array('rows' => 6, 'cols' => 74)
        );

        // validation form rules
        $this->addRule('releaseState', 'The state of the new release is required'    , 'required');
        $this->addRule('releaseVersion', 'The version of the new release is required', 'required');
        $this->addRule('releaseNotes', 'Notes of the new release is required'        , 'required');
        $this->addRule('APIState', 'The state of the current API is required'        , 'required');
        $this->addRule('APIVersion', 'The version of the current API is required'    , 'required');

        $licenseRules['content'][0] = array('License content is required', 'required');
        $this->addGroupRule('releaseLicense', $licenseRules);

        // Buttons of the wizard to do the job
        $this->buildButtons(array('commit'));
    }
}
?>