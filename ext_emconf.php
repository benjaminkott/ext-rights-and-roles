<?php

/*
 * This file is part of the package bk2k/rights-and-roles.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

$EM_CONF[$_EXTKEY] = [
    'title' => 'Rights and Roles',
    'description' => 'Extension for enhanced Rights and Roles',
    'category' => 'plugin',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.4.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'BK2K\\RightsAndRoles\\' => 'Classes'
        ],
    ],
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'author' => 'Benjamin Kott',
    'author_email' => 'info@bk2k.info',
    'author_company' => 'private',
    'version' => '0.0.1',
];
