<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'BSS Meteo Widget',
    'description' => 'MeteoSwiss NOWCAST Weather Widget for TYPO3 13.4',
    'category' => 'plugin',
    'author' => 'Bermuda Software Solutions',
    'author_email' => 'anton.marchenko@bermuda-software.ch',
    'state' => 'beta',
    'clearCacheOnLoad' => true,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-13.99.99',
            'content_blocks' => '13.4.0-13.99.99'
        ]
    ]
];
