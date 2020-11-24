<?php

/*
 * This file is part of the package bk2k/rights-and-roles.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

defined('TYPO3_MODE') || die('Access denied.');

/**
 * Module Rights and Roles
 */
$firstKey = array_key_first($GLOBALS['TBE_MODULES']);
$firstValue = array_shift($GLOBALS['TBE_MODULES']);
$GLOBALS['TBE_MODULES'] = array_merge([$firstKey => $firstValue, 'rightsandroles' => ''], $GLOBALS['TBE_MODULES']);
$GLOBALS['TBE_MODULES']['_configuration']['rightsandroles'] = [
    'labels' => 'LLL:EXT:rights_and_roles/Resources/Private/Language/locallang_mod.xlf',
    'name' => 'rightsandroles',
    'iconIdentifier' => 'module-rightsandroles'
];

if (empty($_EXTKEY)) {
    $_EXTKEY = 'rights_and_roles';
}

/**
 * Module Rights and Roles > Matrix
 */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'BK2K.' . $_EXTKEY,
    'rightsandroles',
    'rightsandroles_matrix',
    'top',
    [
        'RightsAndRoles' => 'matrix',
    ],
    [
        'access' => 'admin',
        'labels' => 'LLL:EXT:rights_and_roles/Resources/Private/Language/locallang_mod_matrix.xlf',
        'icon'   => 'EXT:rights_and_roles/Resources/Public/Icons/module-rnrmatrix.svg',
    ]
);

/**
 * Module Rights and Roles > Access
 */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'BK2K.' . $_EXTKEY,
    'rightsandroles',
    'rightsandroles_access',
    'top',
    [
        'RightsAndRoles' => 'access',
    ],
    [
        'access' => 'admin',
        'labels' => 'LLL:EXT:rights_and_roles/Resources/Private/Language/locallang_mod_access.xlf',
        'icon'   => 'EXT:rights_and_roles/Resources/Public/Icons/module-access.svg',
    ]
);
