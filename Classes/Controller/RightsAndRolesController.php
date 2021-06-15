<?php
declare(strict_types = 1);

/*
 * This file is part of the package bk2k/rights-and-roles.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace BK2K\RightsAndRoles\Controller;

use BK2K\RightsAndRoles\Domain\Model\RightsAndRolesMapping;
use BK2K\RightsAndRoles\Utility\TcaSelectItemsInjectHelper;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Extbase\Domain\Repository\BackendUserGroupRepository;
use TYPO3\CMS\Extbase\Domain\Repository\BackendUserRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class RightsAndRolesController extends ActionController
{
    /**
     * @var BackendUserRepository
     */
    protected $backendUserRepository;

    /**
     * @var BackendUserGroupRepository
     */
    protected $backendUserGroupRepository;

    /**
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * @param BackendUserRepository $backendUserRepository
     */
    public function injectBackendUserRepository(BackendUserRepository $backendUserRepository): void
    {
        $this->backendUserRepository = $backendUserRepository;
    }

    /**
     * @param BackendUserGroupRepository $backendUserGroupRepository
     */
    public function injectBackendUserGroupRepository(BackendUserGroupRepository $backendUserGroupRepository): void
    {
        $this->backendUserGroupRepository = $backendUserGroupRepository;
    }

    /**
     * @param PageRepository $pageRepository
     */
    public function injectPageRepository(\TYPO3\CMS\Core\Domain\Repository\PageRepository $pageRepository): void
    {
        $this->pageRepository = $pageRepository;
    }

    /**
     * @return void
     */
    public function matrixAction()
    {
        $this->view->assign('groups', $this->getBackendUserGroups());
        $this->view->assign('rights', $this->getRightsList());
    }

    /**
     * Renders the access actions.
     * @return void
     */
    public function accessAction()
    {
        $accessList = [];
        $accessConfiguration = $this->getAccessConfiguration();

        foreach ($accessConfiguration as $groupId => $pages) {
            $backendGroup = $this->backendUserGroupRepository->findByIdentifier($groupId);
            $accessList[$groupId] = [
                'title' => $backendGroup->getTitle(),
            ];
            foreach ($pages as $pageId => $permissions) {
                $title = 'global';
                if ($pageId !== 0) {
                    $record = BackendUtility::getRecord('pages', $pageId);
                    if ($record) {
                        $title = $record['title'];
                    }
                }
                $accessList[$groupId]['pages'][$pageId] = [
                    'title' => $title,
                    'rights' => [
                        'page_read'    => $this->hasAccess($permissions, Permission::PAGE_SHOW),
                        'page_edit'    => $this->hasAccess($permissions, Permission::PAGE_EDIT),
                        'content_edit' => $this->hasAccess($permissions, Permission::CONTENT_EDIT),
                        'page_delete'  => $this->hasAccess($permissions, Permission::PAGE_DELETE),
                        'page_new'     => $this->hasAccess($permissions, Permission::PAGE_NEW),
                        'decimal'      => $permissions
                    ]
                ];
            }
        }

        $this->view->assign('accessList', $accessList);
        $this->view->assign('accessRaw', $this->getRawAccessConfiguration());
    }

    /**
     * Checks the access for given permissions.
     * @param int $permissions the permissions from configuration
     * @param int $permission  the requested configuration
     * @return bool
     */
    private function hasAccess($permissions, $permission)
    {
        return ($permissions & $permission) == $permission;
    }

    private function getAccessConfiguration(): array
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXT']['page']['access'] ?? [];
    }

    /**
     * Returns the raw access configuration in yaml representation.
     * @return string
     */
    private function getRawAccessConfiguration()
    {
        $raw  = 'EXT:' . PHP_EOL;
        $raw .= '  pages:' . PHP_EOL;
        $raw .= '    access:' . PHP_EOL;
        foreach ($this->getAccessConfiguration() as $groupId => $pages) {
            $raw .= '      ' . $groupId . ':' . PHP_EOL;
            foreach ($pages as $pageId => $permissions) {
                $raw .= '        ' . $pageId . ': ' . $permissions . PHP_EOL;
            }
        }

        return $raw;
    }

    /**
     * Returns all groups masks with [G] as groups.
     * @return RightsAndRolesMapping[]
     */
    private function getBackendUserGroups()
    {
        $rights = $this->getRightsList();
        $list   = [];
        foreach ($this->filterGroups('[G]') as $group) {
            $list[] = new RightsAndRolesMapping($group, $rights);
        }

        return $list;
    }

    /**
     * Returns all groups masks with [R] as roles.
     * @return \TYPO3\CMS\Extbase\Domain\Model\BackendUserGroup[]
     */
    private function getBackendUserRoles()
    {
        return $this->filterGroups('[R]');
    }

    /**
     * Filters the list of groups by filterName and in title.
     * @param string $filterName
     * @return array
     */
    private function filterGroups($filterName)
    {
        $list = [];
        foreach ($this->getAllBackendUserGroups() as $group) {
            if (strpos($group->getTitle(), $filterName) !== false) {
                $list[] = $group;
            }
        }
        return $list;
    }

    /**
     * Returns all backend user groups.
     * @return \TYPO3\CMS\Extbase\Domain\Model\BackendUserGroup[]
     */
    private function getAllBackendUserGroups()
    {
        return $this->backendUserGroupRepository->findAll()->toArray();
    }

    /**
     * @return array
     */
    private function getRightsList()
    {
        $tcaSelectItem = new TcaSelectItemsInjectHelper();
        $configuration = $tcaSelectItem->getSpecialFieldsConfiguration();

        return $configuration;
    }
}
