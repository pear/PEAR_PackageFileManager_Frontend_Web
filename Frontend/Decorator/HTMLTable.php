<?php
/**
 * HTML_Table Decorator for PEAR_PackageFileManager_Frontend
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    CVS: $Id$
 * @since      File available since Release 0.1.0
 */

require_once 'PEAR/PackageFileManager/Frontend/Decorator.php';
require_once 'HTML/QuickForm.php';
require_once 'HTML/Table.php';

/**
 * Decorator to help with fetching html_table representations of
 * replacements, roles and exceptions.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 * @abstract
 */

class PEAR_PackageFileManager_Frontend_Decorator_HTMLTable extends PEAR_PackageFileManager_Frontend_Decorator
{
    /**
     * Decorator container
     * @var object HTML_Table object
     */
    var $table;

    /**
     * Decorator constructor
     * @param  object    $fe       PEAR_PackageFileManager_Frontend object
     * @param  object    $table    HTML_Table object
     * @since  0.1.0
     * @access public
     */
    function PEAR_PackageFileManager_Frontend_Decorator_HTMLTable(&$fe)
    {
        parent::PEAR_PackageFileManager_Frontend_Decorator($fe);
        $this->table = false;
    }

    /**
     * Gets instance of HTML_Table object
     *
     * @since  0.1.0
     * @access public
     */
    function &getHtmlTable()
    {
        return $this->table;
    }

    /**
     * Sets instance of HTML_Table object
     *
     * @since  0.1.0
     * @access public
     */
    function setHtmlTable(&$table)
    {
        $this->table =& $table;
    }

    /**
     * Decorator::getMaintList()
     *
     * @see    PEAR_PackageFileManager_Frontend::getMaintList()
     * @since  0.1.0
     * @access public
     */
    function getMaintList($users, $columns, $rowscope = 1, $ckid = 'users')
    {
        $maintainers = $this->fe->getMaintList($users);
        $this->_buildList($maintainers, $columns, $rowscope, $ckid);
    }

    /**
     * Decorator::getFileList()
     *
     * @param  boolean  $default  if we get initial data set at first run
     * @param  boolean  $ignore   Either if you want all files or just ignored
     * @param  string   $plugin   PEAR_PackageFileManager filelist generator
     * @return array
     * @since  0.1.0
     * @access public
     */
    function getFileList($default, $ignore, $plugin, $columns, $rowscope = 1, $ckid = 'files')
    {
        $datasrc = $this->fe->getFileList($default, $ignore, $plugin);

        $filelist = array();
        foreach ($datasrc['mapping'] as $k => $filename) {
            $number = count($datasrc[$k]['replacements']);
            $filelist[$k] = array('path' => $filename, 'replaces' => $number);
        }
        $this->_buildList($filelist, $columns, $rowscope, $ckid);
    }

    /**
     * Decorator::getDepList()
     *
     * @since  0.1.0
     * @access public
     */
    function getDepList($columns, $rowscope = 4, $ckid = 'deps')
    {
        $deps = $this->fe->getDepList();
        $this->_buildList($deps, $columns, $rowscope, $ckid);
    }

    /**
     * Decorator::getExceptionList()
     *
     * @since  0.1.0
     * @access public
     */
    function getExceptionList($columns, $rowscope = 1, $ckid = 'files')
    {
        $datasrc = $this->fe->getFileList();

        $filelist = array();
        foreach ($datasrc['mapping'] as $k => $filename) {
            $filelist[$k] = array('path' => $filename, 'role' => $datasrc[$k]['role']);
        }
        $this->_buildList($filelist, $columns, $rowscope, $ckid);
    }

    /**
     * Returns the table structure as HTML
     *
     * @since  0.1.0
     * @access public
     */
    function toHtml()
    {
        return $this->table->toHtml();
    }

    /**
     * Builds selection list
     *
     * @param  mixed   $datasrc  Data source
     * @param  array   $columns  Names list of each column
     * @return object   instance of html_table
     * @since  0.1.0
     * @access private
     */
    function _buildList($datasrc, $columns, $rowscope, $ckid)
    {
        $thead = &$this->table->getHeader();
        $tbody = &$this->table->getBody();
        $tfoot = &$this->table->getFooter();

        // add column for selection by checkboxes
        array_unshift($columns, '&nbsp;');
        $cc1 = count($columns);

        // add header cells of outer table
        $attr = array();
        for($c = 0; $c < $cc1; $c++) {
            $attr[$c] = 'class="'.$ckid.'th'.($c+1).'" scope="col"';
        }
        $thead->addRow($columns, $attr, 'th');

        $t2 = new HTML_Table(array('class' => 'tabletwo'));
        // add body contents of inner table
        if (is_array($datasrc)) {
            foreach ($datasrc as $id => $data) {
                $ck[$id] = &HTML_QuickForm::createElement('checkbox', $ckid."[$id]", null, null, array('id' => $ckid));
                $contents = array();
                foreach($columns as $k => $col) {
                    if ($k == 0) {
                        $contents[] = $col;
                    } else {
                        $contents[] = $data[strtolower($columns[$k])];
                    }
                }
                $r = $t2->addRow($contents);
                $t2->setCellContents($r, 0, $ck[$id]);
            }

            $cc2 = $t2->getColCount(0);
            $attr = array();
            for($c = 0; $c < $cc2; $c++) {
                if ($c == $rowscope) {
                    $attr[$c] = 'class="'.$ckid.'td'.($c+1).'" scope="row"';
                } else {
                    $attr[$c] = 'class="'.$ckid.'td'.($c+1).'"';
                }
            }
            $t2->updateRowAttributes(0, $attr);

            // alternate row colors
            $altRow1 = array('class' => 'odd');
            $altRow2 = array('class' => 'even');
            $t2->altRowAttributes(0, $altRow1, $altRow2, true);

        }

        // add footer cells of outer table
        $rowCount = $t2->getRowCount();
        if ($rowCount == 0) {
            $r = $t2->addRow(array('&nbsp;'), array('colspan' => $cc1, 'style' => 'background-color:transparent;'));
        } else {
            $ftr = array("$rowCount ". (($rowCount > 1) ? $ckid: substr($ckid, 0, -1)));
            $r = $tfoot->addRow($ftr);
            $tfoot->setCellAttributes($r, 0, array('colspan' => $cc1, 'class' => 'total'));

            $ftr  = '<img src="'. $_SERVER['PHP_SELF'] .'?arrow_ltr" border="0" alt="^--" />';
            $ftr .= " [<a href=\"javascript:doSelection('$ckid', 1);\">Select All</a>]";
            $ftr .= " [<a href=\"javascript:doSelection('$ckid', 0);\">Select None</a>]";
            $ftr .= " [<a href=\"javascript:doSelection('$ckid', 2);\">Toggle Selection</a>]";

            $ftr = array($ftr);
        }
        $r = $tfoot->addRow($ftr);
        $tfoot->setCellAttributes($r, 0, array('colspan' => $cc1));

        // add body cells of inner table
        $innerd = "<div class=\"autoscroll\">\n".$t2->toHtml()."\n</div>";
        $tbody->addRow(array($innerd), array('colspan' => $cc1));
    }
}
?>