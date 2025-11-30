<?php
defined('TYPO3') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'bss_meteo_widget',
    'Configuration/TypoScript',
    'BSS Meteo Widget'
);
