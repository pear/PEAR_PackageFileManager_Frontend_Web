/**
 * JavaScript used to manage selection with checkboxes,
 * in lists of PEAR_PackageFileManager Web frontend.
 *
 * @category   PEAR
 * @package    PEAR_PackageFileManager_Frontend_Web
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @copyright  2005-2006 Laurent Laville
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    CVS: $Id: doSelection.js,v 1.1 2006-05-12 16:43:36 farell Exp $
 * @since      File available since Release 0.1.0
 */

function doSelection(checkWhat, checkMode) {
    if (checkMode !== 0 && checkMode !== 1 && checkMode !== 2) {
        return;
    }

    // Find all the checkboxes...
    var inputs = document.getElementsByTagName('input');

    // Loop through all form elements (input tags)
    for(index = 0; index < inputs.length; index++) {
        // ...if it's the type of checkbox we're looking for, change its checked status
        if (inputs[index].id == checkWhat && inputs[index].type == 'checkbox') {
            if (checkMode == 2) {
                if (inputs[index].checked == 0) {
                    inputs[index].checked = 1;
                } else if (inputs[index].checked == 1) {
                    inputs[index].checked = 0;
                }
            } else {
                inputs[index].checked = checkMode;
            }
        }
    }
}

