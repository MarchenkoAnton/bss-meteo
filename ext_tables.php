<?php
defined('TYPO3') or die();

$GLOBALS['TCA']['tt_content']['types']['bermuda-meteo'] = [
    'showitem' => '
        --div--;General,
            --palette--;;general,
            bodytext,
        --div--;Content,
            pi_flexform,
    '
];
