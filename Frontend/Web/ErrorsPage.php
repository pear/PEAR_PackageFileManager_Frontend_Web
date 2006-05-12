<?php
/**
 * Creates the page for displaying errors/warnings on trying to generate new package.
 *
 * Errors and/or warning are issues only from package generation
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
 * Creates the page for displaying errors/warnings on trying to generate new package.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class ErrorsPage extends TabbedPage
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
        $this->addElement('header', null, 'Error and Warning messages');

        // Get all warnings and errors.
        if (PEAR_PackageFileManager_Frontend::hasErrors() ) {
            $warn = PEAR_PackageFileManager_Frontend::getErrors(true);
            $messages = '<ol>';
            foreach($warn as $warning) {
                $messages .= '<li>' . $warning['message'] . '</li>';
            }
            $messages .= '</ol>';
        } else {
            $messages = 'Stack Empty';
        }

        // We need a simple static html area for ordering messages list.
        $div = '<div class="autoscroll">' . $messages . '</div>';
        $this->addElement('static', 'errors', '', $div);

        // Buttons of the wizard to do the job
        $this->buildButtons(array('reset','commit'));
    }
}
?>