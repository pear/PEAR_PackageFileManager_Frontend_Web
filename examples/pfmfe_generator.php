<?php
/**
 * PEAR_PackageFileManager_Fontend_Web generator
 *
 * @version   $Id$
 * @author    Laurent Laville <pear@laurent-laville.org>
 * @link      http://pear.php.net/package/PEAR_PackageFileManager_Frontend_Web
 * @ignore
 */

session_start();

if (count($_SESSION) == 0) {

    require_once 'HTML/QuickForm.php';

    if (version_compare(phpversion(), '5.0.0', '<')) {
        require_once 'PHP/Compat.php';
        PHP_Compat::loadFunction('file_put_contents');
    }

    function _validate_pfmfegen($values)
    {
        $errors = array();

        if (empty($values['packagedir'])) {
            $errors['packagedir'] = 'Package file name or directory is required';
        } elseif (!file_exists($values['packagedir'])) {
            $errors['packagedir'] = 'Package file name or directory does not exists';
        }
        return empty($errors)? true: $errors;
    }

    $pfmfegen =& new HTML_QuickForm('pfmfe');
    $pfmfegen->removeAttribute('name'); // XHTML compliant

    $pfmfegen->addElement('header', 'pfmfegen', 'PEAR_PackageFileManager_Frontend :: Generator');
    $homepage = 'http://pear.laurent-laville.org/PEAR_PackageFileManager/';
    $pfmfegen->addElement('link', 'PFMWebsite', 'Home page :', $homepage, $homepage);

    // --- Choose an external config file -------------------------------------
    $configFile =& $pfmfegen->addElement('text', 'config', 'Config file:');
    $configFile->setSize(50);

    // --- Choose a language --------------------------------------------------
    $langcodes = array(
        "XML" => "XML",
        "phpArray" => "Php Array",
        "ini" => "INI",
        "iniCommented" => "INI with comments",
    );
    $language =& $pfmfegen->addElement('select', 'language', 'Choose a language:');
    $language->load($langcodes);

    // --- Choose a package file or directory ---------------------------------
    $packageFile =& $pfmfegen->addElement('text', 'packagedir', 'Package files directory:');
    $packageFile->setSize(50);


    // --- Options ------------------------------------------------------------
    //*
    $baseinstalldir =& $pfmfegen->addElement('text', 'baseinstalldir', 'Base install directory :');
    $baseinstalldir->setSize(50);

    //*
    $exportcompatiblev1 =& $pfmfegen->addElement('checkbox', 'exportcompatiblev1');
    $exportcompatiblev1->setLabel('package.xml 1.0 :');
    $exportcompatiblev1->setText('export compatible');

    //*
    $changelogoldtonew =& $pfmfegen->addElement('checkbox', 'changelogoldtonew');
    $changelogoldtonew->setLabel('ChangeLog :');
    $changelogoldtonew->setText('list from oldest entry to newest');

    //*
    $simpleoutput =& $pfmfegen->addElement('checkbox', 'simpleoutput');
    $simpleoutput->setLabel('Human readable :');
    $simpleoutput->setText('');

    //*
    $outputdirectory =& $pfmfegen->addElement('text', 'outputdirectory', 'Generated package.xml directory :');
    $outputdirectory->setSize(50);

    // --- Save the preferences to file ---------------------------------------
    $tofile =& $pfmfegen->addElement('text', 'tofile', 'Save the preferences to file:');
    $tofile->setSize(50);

    $filetype =& $pfmfegen->addElement('select', 'filetype', 'Choose a language:');
    $filetype->load($langcodes);

    // default values
    $pfmfegen->setDefaults(array(
        'language'           => 'XML',
        'packagedir'         => dirname(__FILE__),
        'exportcompatiblev1' => true,
        'changelogoldtonew'  => false,
        'simpleoutput'       => true
        ));

    // form filters
    $pfmfegen->applyFilter('__ALL__', 'trim');

    // form rules
    $pfmfegen->addFormRule('_validate_pfmfegen');

    // --- Configuration complete ---------------------------------------------
    $buttons[] =& $pfmfegen->createElement('submit', 'send', 'Generate');
    $buttons[] =& $pfmfegen->createElement('reset', 'reset', 'Reset');
    $pfmfegen->addGroup($buttons, 'buttons', '', '&nbsp;', false);

    // ========================================================================
    $valid = $pfmfegen->validate();
    if (!$valid) {
        $pfmfegen->display();

    } else {
        $safe = $pfmfegen->exportValues();
        $_SESSION['pfmfegen'] = $safe;
    }
}

if (count($_SESSION)) {

    require_once 'PEAR/PackageFileManager/Frontend.php';

    $safe = $_SESSION['pfmfegen'];
    $pkgDir = $safe['packagedir'];

    $web =& PEAR_PackageFileManager_Frontend::singleton('Web', $pkgDir);

    if (empty($safe['config'])) {
        $web->setOption('baseinstalldir', $safe['baseinstalldir']);
        $web->setOption('exportcompatiblev1', (bool) $safe['exportcompatiblev1']);
        $web->setOption('changelogoldtonew', (bool) $safe['changelogoldtonew']);
        $web->setOption('simpleoutput', (bool) $safe['simpleoutput']);
        $web->setOption('outputdirectory',
            empty($safe['outputdirectory']) ? false: $safe['outputdirectory']);
    } else {
        $config = array($safe['config'], $safe['language']);
        $web->loadPreferences($config);
    }

    if (!empty($safe['tofile'])) {
        $web->savePreferences($safe['tofile'], $safe['filetype']);
    }

    if ($web->hasErrors('error')) {
        $errors = $web->getErrors();
        echo '<pre>';
        var_dump($errors);
        echo '</pre>';
        die('exit on Error');
    }
    $web->run();
}
?>