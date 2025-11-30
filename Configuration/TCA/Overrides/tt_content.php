<?php
defined('TYPO3') or die();

call_user_func(function () {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaColumns('tt_content', [
        // поле point_id
    ]);

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
        [
            'Weather Widget MeteoSwiss',
            'bss_meteo_widget',
            'content-bss-meteo-widget'
        ],
        'CType',
        'bss_meteo_widget'
    );

    $GLOBALS['TCA']['tt_content']['types']['bss_meteo_widget'] = [
        'showitem' =>
            '--palette--;;general,' .
            '--palette--;;headers,' .
            'pi_flexform;Settings,' .
            '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,' .
            '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,' .
            '--palette--;;hidden'
    ];

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
        'bss_meteo_widget',
        'FILE:EXT:bss_meteo_widget/Configuration/FlexForms/WeatherWidget.xml'
    );
});
