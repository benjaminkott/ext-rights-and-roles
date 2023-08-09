<?php

/*
 * This file is part of the package bk2k/rights-and-roles.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use BK2K\RightsAndRoles\Controller\RightsAndRolesController;

return [
    'rightsandroles' => [
        'position' => ['after' => 'web'],
        'labels' => 'LLL:EXT:rights_and_roles/Resources/Private/Language/locallang_mod.xlf',
        'iconIdentifier' => 'module-rightsandroles',
    ],
    'rightsandroles_matrix' => [
        'parent' => 'rightsandroles',
        'access' => 'admin',
        'path' => '/module/rightsandroles/matrix',
        'iconIdentifier' => 'module-rightsandroles-matrix',
        'labels' => 'LLL:EXT:rights_and_roles/Resources/Private/Language/locallang_mod_matrix.xlf',
        'extensionName' => 'RightsAndRoles',
        'controllerActions' => [
            RightsAndRolesController::class => [
                'matrix',
            ],
        ],
    ],
    'rightsandroles_access' => [
        'parent' => 'rightsandroles',
        'access' => 'admin',
        'path' => '/module/rightsandroles/access',
        'iconIdentifier' => 'module-rightsandroles-access',
        'labels' => 'LLL:EXT:rights_and_roles/Resources/Private/Language/locallang_mod_access.xlf',
        'extensionName' => 'RightsAndRoles',
        'controllerActions' => [
            RightsAndRolesController::class => [
                'access',
            ],
        ],
    ]
];
