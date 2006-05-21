<?php
/**
 * Creates the page to display a xml code preview of your package.
 *
 * Show a preview of your new package.xml version, and check for any error.
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
 * Creates the page to display a xml code preview of your package.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class PreviewPage extends TabbedPage
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
        $this->addElement('header', null, 'package.xml Preview');

        $pageName = $this->getAttribute('id');
        // Checks whether the pages, before this one, are valid
        $QFCvalid = $this->controller->isValid($pageName);

        if (!$QFCvalid) {
            // jump to first page invalid
            $pageName = $this->controller->findInvalid();
            $page =& $this->controller->getPage($pageName);
            return $page->handle('jump');
        }

        $fe =& PEAR_PackageFileManager_Frontend::singleton();

        // Get the package.xml preview and check for errors.
        $preview = $fe->buildPackageFile(true,
            $fe->getOption('exportcompatiblev1'),
            $fe->getOption('changelogoldtonew'),
            $fe->getOption('simpleoutput')
        );
        if (!$preview) {
            // jump to the warnings page.
            $pageName = $fe->getPageName('page0');
            $page =& $this->controller->getPage($pageName);
            return $page->handle('jump');
        }
        $preview = substr($preview, strpos($preview,'<pre>&lt;?xml'));

        $available = PEAR_PackageFileManager2::isIncludeable('Text/Highlighter.php');
        if ($available) {
            include_once 'Text/Highlighter.php';
            $hl =& Text_Highlighter::factory('XML');
            $preview = str_replace(array('<pre>','</pre>'), array('', ''), $preview);
            $preview = html_entity_decode($preview);
            $xml = $hl->highlight($preview);
        } else {
            $xml = $preview;
        }

        // We need a simple checkbox for the XML package v1.
        $this->addElement('checkbox', 'exportCompatibleV1', 'XML version:', 'Export compatible version 1.0');

        // We need a simple checkbox for the changelog order option.
        $this->addElement('checkbox', 'changelogOldToNew', 'ChangeLog order:', 'From oldest entry to newest');

        // We need a simple checkbox for the simpleoutput option.
        $this->addElement('checkbox', 'simpleOutput', 'File List:', 'Human readable');

        // We need a simple static html area for the package xml structure.
        $div = '<div class="autoscroll">' . $xml . '</div>';
        $this->addElement('static', 'packageXML', '', $div);

        // Buttons of the wizard to do the job
        $this->buildButtons(array('reset'));

        // default options
        $def = array(
            'exportCompatibleV1' => $fe->getOption('exportcompatiblev1'),
            'changelogOldToNew'  => $fe->getOption('changelogoldtonew'),
            'simpleOutput'       => $fe->getOption('simpleoutput')
            );
        $this->setDefaults($def);
    }
}
?>