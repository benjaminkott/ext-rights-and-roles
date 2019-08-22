<?php
declare(strict_types = 1);

/*
 * This file is part of the package bk2k/rights-and-roles.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace BK2K\RightsAndRoles\Hook;

use TYPO3\CMS\Core\Type\Bitmask\Permission;

/**
 * Hook for overloading the access list rights for groups and pages.
 *
 * @example
 * You have to add the following structure in your configuration:
 *  EXT
 *    page:
 *      debug: 1          # Debug mode flag
 *      access:           # the Access configuration
 *        13:
 *          0: 1
 *
 * This means, that the group "13" (in this example a base group for simple editor) can read (decimal 1, binary 1)
 * all pages (0). The rights are calulated by standard binary additions (@see \TYPO3\CMS\Core\Type\Bitmask\Permission):
 *   NOTHING: 0       (@see \TYPO3\CMS\Core\Type\Bitmask\Permission::NOTHING)
 *   PAGE_SHOW: 1     (@see \TYPO3\CMS\Core\Type\Bitmask\Permission::PAGE_SHOW)
 *   PAGE_EDIT: 2     (@see \TYPO3\CMS\Core\Type\Bitmask\Permission::PAGE_EDIT)
 *   PAGE_DELETE: 4   (@see \TYPO3\CMS\Core\Type\Bitmask\Permission::PAGE_DELETE)
 *   PAGE_NEW: 8      (@see \TYPO3\CMS\Core\Type\Bitmask\Permission::PAGE_NEW)
 *   CONTENT_EDIT: 16 (@see \TYPO3\CMS\Core\Type\Bitmask\Permission::CONTENT_EDIT)
 *   ALL: 31          (@see \TYPO3\CMS\Core\Type\Bitmask\Permission::ALL)
 *
 * If a usergroup can view the page and should be able to edit the content, the correct binary value should be
 * "PAGE_SHOW | CONTENT_EDIT", which is calculated in decimal 17.
 *
 * If a usergroup should have a specific access of a single page, you can add this page ID in the group block:
 *  EXT
 *    page:
 *      access:
 *        13:
 *          25: 19
 * With this configuration the usergroup "13" can "PAGE_SHOW && PAGE_EDIT && CONTENT_EDIT" for the Page with ID "25".
 *
 * You can also combine the default page "0" (every page) with specific Page ID like:
 *  EXT
 *    page:
 *      access:
 *        13:
 *          0: 1
 *          25: 19
 *
 *
 */
class BackendUserGroupRightsHook
{
    /**
     * @var array
     */
    private $configuration = [];

    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Logs a dev message to log if debug mode is available.
     * @param string $message the log message
     * @return void
     */
    private function devLog($message)
    {
        if ($this->getDebugConfiguration()) {
            error_log($message);
        }
    }

    /**
     * Calculates the permissions.
     * @param array $params
     * @return mixed
     */
    public function calcPerms(array $params)
    {
        $access = $this->getAccessConfiguration();
        $usergroups = $this->getUserGroups();

        if ($access && is_array($usergroups)) {
            $output = false;
            foreach ($usergroups as $usergroup) {
                if (isset($access[$usergroup])) {
                    $beUserGroupConfiguration = $access[$usergroup];
                    $pageId = isset($params['row']['uid']) ? $params['row']['uid'] : 0;
                    $accessConfiguration = isset($beUserGroupConfiguration[$pageId]) ? $beUserGroupConfiguration[$pageId] : $beUserGroupConfiguration[0];

                    if ($output === false) {
                        $output = Permission::NOTHING;
                    }

                    $output |= $accessConfiguration;
                }
            }
            if ($output !== false) {
                return $output;
            }
        }

        return $params['outputPermissions'];
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function getPagePermsClause(array $params)
    {
        // Parse the params
        $currentClause = $params['currentClause'];
        $permissions   = $params['perms'];

        $access = $this->getAccessConfiguration();
        $usergroups = $this->getUserGroups();

        if ($access && is_array($usergroups)) {
            foreach ($usergroups as $usergroup) {
                if (isset($access[$usergroup])) {
                    $beUserGroupConfiguration = $access[$usergroup];
                    $pageId = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
                    $accessConfiguration = isset($beUserGroupConfiguration[$pageId]) ? $beUserGroupConfiguration[$pageId] : $beUserGroupConfiguration[0];
                    $granted = (($permissions & $accessConfiguration) == $accessConfiguration);

                    if ($granted) {
                        return ' 1';
                    }
                }
            }
        }

        return $currentClause;
    }

    /**
     * @return int[]
     */
    private function getUserGroups()
    {
        return $GLOBALS['BE_USER']->userGroupsUID;
    }

    private function getAccessConfiguration(): bool
    {
        $pageConfiguration = $this->configuration['EXT']['page'];

        return isset($pageConfiguration['access']) ? $pageConfiguration['access'] : false;
    }

    private function getDebugConfiguration(): bool
    {
        $pageConfiguration = $this->configuration['EXT']['page'];

        return isset($pageConfiguration['debug']) ? $pageConfiguration['debug'] : false;
    }
}
