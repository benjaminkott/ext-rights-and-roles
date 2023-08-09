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
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Beuser\Domain\Repository\BackendUserGroupRepository;
use TYPO3\CMS\Beuser\Domain\Repository\BackendUserRepository;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Extbase\Domain\Model\BackendUserGroup;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class RightsAndRolesController extends ActionController
{
    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected BackendUserRepository $backendUserRepository;
    protected BackendUserGroupRepository $backendUserGroupRepository;
    protected PageRepository $pageRepository;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory,
        BackendUserRepository $backendUserRepository,
        BackendUserGroupRepository $backendUserGroupRepository,
        PageRepository $pageRepository
    ) {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->backendUserRepository = $backendUserRepository;
        $this->backendUserGroupRepository = $backendUserGroupRepository;
        $this->pageRepository = $pageRepository;
    }

    public function matrixAction(): ResponseInterface
    {
        $this->view->assign('groups', $this->getBackendUserGroups());
        $this->view->assign('rights', $this->getRightsList());

        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->setContent($this->view->render());

        return $this->htmlResponse($moduleTemplate->renderContent());
    }

    public function accessAction(): ResponseInterface
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

        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->setContent($this->view->render());

        return $this->htmlResponse($moduleTemplate->renderContent());
    }

    private function hasAccess(int $permissions, int $permission): bool
    {
        return ($permissions & $permission) == $permission;
    }

    private function getAccessConfiguration(): array
    {
        return $GLOBALS['TYPO3_CONF_VARS']['EXT']['page']['access'] ?? [];
    }

    private function getRawAccessConfiguration(): string
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
     * @return RightsAndRolesMapping[]
     */
    private function getBackendUserGroups(): array
    {
        $rights = $this->getRightsList();
        $list   = [];
        foreach ($this->filterGroups('[G]') as $group) {
            $list[] = new RightsAndRolesMapping($group, $rights);
        }

        return $list;
    }

    /**
     * @return BackendUserGroup[]
     */
    private function getBackendUserRoles(): array
    {
        return $this->filterGroups('[R]');
    }

    private function filterGroups(string $filterName): array
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
     * @return BackendUserGroup[]
     */
    private function getAllBackendUserGroups(): array
    {
        return $this->backendUserGroupRepository->findAll()->toArray();
    }

    private function getRightsList(): array
    {
        $tcaSelectItem = new TcaSelectItemsInjectHelper();
        $configuration = $tcaSelectItem->getSpecialFieldsConfiguration();

        return $configuration;
    }
}
