<?php
/**
 * Default display driver that used the basic QF renderer.
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
 * Default display driver that used the basic QF renderer.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class ActionDisplay extends HTML_QuickForm_Action_Display
{
    /**
     *
     * @since  0.1.0
     * @access private
     */
    function _renderForm(&$page)
    {

        $formTemplate = "\n<form{attributes}>"
                      . "\n<table class=\"maintable\">"
                      . "\n{content}"
                      . "\n</table>"
                      . "\n</form>";

        $headerTemplate = "\n<tr>"
                        . "\n\t<td class=\"hdr\" colspan=\"2\">"
                        . "\n\t\t{header}"
                        . "\n\t</td>"
                        . "\n</tr>";

        $elementNavig    = "\n<tr valign=\"top\">"
                         . "\n\t<td colspan=\"2\">"
                         . "\n\t{element}"
                         . "\n\t</td>"
                         . "\n</tr>";

        $elementTemplate = "\n<tr>"
                         . "\n\t<td class=\"form-label\"><!-- BEGIN required --><span style=\"color: #ff0000\">*</span>&nbsp;<!-- END required --><!-- BEGIN label --><b>{label}</b><!-- END label --></td>"
                         . "\n\t<td class=\"form-input\">{element}<!-- BEGIN error --><br /><span style=\"color: #ff0000\">{error}</span><!-- END error --></td>"
                         . "\n</tr>";

        $groupTemplate = "\n\t\t<table cellspacing=\"0\">"
                       . "\n\t\t<tr>"
                       . "\n\t\t\t{content}"
                       . "\n\t\t</tr>"
                       . "\n\t\t</table>\n\t";

        $groupElementTemplate = "<td valign=\"top\">{element}"
                              . "<!-- BEGIN label --><br />"
                              . "<span style=\"font-size:90%;\">{label}<!-- BEGIN required --><span style=\"color: #ff0000\">*</span><!-- END required --></span>"
                              . "<!-- END label -->"
                              . "</td>";

        $renderer =& $page->defaultRenderer();

        $renderer->setFormTemplate($formTemplate);
        $renderer->setHeaderTemplate($headerTemplate);
        $renderer->setElementTemplate($elementTemplate);

        $renderer->setElementTemplate($elementNavig, 'tabs');
        $renderer->setElementTemplate($elementNavig, 'buttons');

        $groups = array('pearInstaller', 'phpVersion',              // on page 1
                        'releaseLicense',                           // on page 2
                        'plugin', 'group',                          // on page 5
                        'filters'                                   // on page 5 and 7
                        );

        foreach ($groups as $grp) {
            $renderer->setGroupTemplate($groupTemplate, $grp);
            $renderer->setGroupElementTemplate($groupElementTemplate, $grp);
        }

        $page->accept($renderer);

        // Package data directory
        $data_dir = '@data_dir@' . DIRECTORY_SEPARATOR
                  . '@package_name@' . DIRECTORY_SEPARATOR;

        $styles = file_get_contents(PEAR_PACKAGEFILEMANAGER_FRONTEND_DATADIR . 'pfmfe.css');
        $js     = file_get_contents($data_dir . 'doSelection.js');
        $body   = $renderer->toHtml();

        $html = <<<HTML
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>PEAR_PackageFileManager Web Frontend</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<style type="text/css">
<!--
$styles
 -->
</style>
<script type="text/javascript">
//<![CDATA[
$js
//]]>
</script>
</head>
<body>
<h1>PEAR_PackageFileManager Web Frontend</h1>
$body
</body>
</html>
HTML;
        echo $html;
    }
}
?>