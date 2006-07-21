<?php
/**
 * Filter list Decorator for PEAR_PackageFileManager_Frontend
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
 * @copyright  2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    CVS: $Id$
 * @since      File available since Release 0.5.0
 */

require_once 'PEAR/PackageFileManager/Frontend/Decorator.php';

/**
 * Decorator to filter list of file.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: @package_version@
 * @since      Class available since Release 0.5.0
 * @abstract
 */

class PEAR_PackageFileManager_Frontend_Decorator_Filter extends PEAR_PackageFileManager_Frontend_Decorator
{
    /**
     * Decorator Filter
     * @var array
     */
    var $filter = array();

    /**
     * Decorator constructor
     *
     * @param  object    $fe       PEAR_PackageFileManager_Frontend object
     * @since  0.5.0
     * @access public
     */
    function PEAR_PackageFileManager_Frontend_Decorator_Filter(&$fe)
    {
        parent::PEAR_PackageFileManager_Frontend_Decorator($fe);
    }

    /**
     * Defines filters to apply
     *
     * @param  array    $filter   Tuple of filter name-value to apply
     * @return void
     * @since  0.5.0
     * @access public
     */
    function setFilters($filter)
    {
        $this->filters = array();
        if (is_array($filter)) {
            foreach($filter as $type => $value) {
                $this->filters[$type] = $value;
            }
        }
    }

    /**
     * Decorator::getFileList()
     *
     * @return array
     * @since  0.5.0
     * @access public
     */
    function getFileList()
    {
        $datasrc = $this->fe->getFileList(true);

        $filelist = array();
        foreach ($datasrc['mapping'] as $k => $filename) {
            $keep = 0;
            foreach ($this->filters as $type => $value) {
                if ($type == 'extension') {
                // apply filter on file extension
                    $pinfo = pathinfo($filename);
                    if (isset($pinfo[$type]) &&
                        ($pinfo[$type] === $value)) {
                        $keep++;
                    } elseif (!isset($pinfo[$type]) &&
                        ($value == '')) {
                        $keep++;
                    }
                } elseif ($type == 'role') {
                // apply filter on file role
                    if ($datasrc[$k]['role'] === $value) {
                        $keep++;
                    }
                } else {
                // keep file on case no filter is given
                    $keep++;
                }
            }
            if ($keep == count($this->filters)) {
                $filelist['mapping'][$k] = $datasrc['mapping'][$k];
                $filelist[$k] = $datasrc[$k];
            }
        }
        return $filelist;
    }
}
?>