<?php

/*
 * This file is part of the package bk2k/rights-and-roles.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use BK2K\RightsAndRoles\Controller\RightsAndRolesController;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die('Access denied.');

/**
 * Module Rights and Roles
 */
ExtensionManagementUtility::addModule(
    'rightsandroles',
    '',
    'after:web',
    null,
    [
        'labels' => 'LLL:EXT:rights_and_roles/Resources/Private/Language/locallang_mod.xlf',
        'name' => 'rightsandroles',
        'iconIdentifier' => 'module-rightsandroles',
    ]
);

ExtensionUtility::registerModule(
    'RightsAndRoles',
    'rightsandroles',
    'rightsandroles_matrix',
    'top',
    [
        RightsAndRolesController::class => 'matrix',
    ],
    [
        'access' => 'admin',
        'labels' => 'LLL:EXT:rights_and_roles/Resources/Private/Language/locallang_mod_matrix.xlf',
        'iconIdentifier' => 'module-rightsandroles-matrix',
    ]
);

ExtensionUtility::registerModule(
    'RightsAndRoles',
    'rightsandroles',
    'rightsandroles_access',
    'top',
    [
        RightsAndRolesController::class => 'access',
    ],
    [
        'access' => 'admin',
        'labels' => 'LLL:EXT:rights_and_roles/Resources/Private/Language/locallang_mod_access.xlf',
        'iconIdentifier' => 'module-rightsandroles-access',
    ]
);
