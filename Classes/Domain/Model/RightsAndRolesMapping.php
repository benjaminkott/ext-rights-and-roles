<?php
declare(strict_types = 1);

/*
 * This file is part of the package bk2k/rights-and-roles.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace BK2K\RightsAndRoles\Domain\Model;

use TYPO3\CMS\Beuser\Domain\Model\BackendUserGroup;

class RightsAndRolesMapping
{
    /**
     * @var BackendUserGroup
     */
    private $backendUserGroup;

    /**
     * @var array
     */
    private $availableRights;

    /**
     * @var array
     */
    private $rights;

    /**
     * @var array
     */
    private $subGroups;

    /**
     * @var string
     */
    private $title;

    public function __construct(BackendUserGroup $backendUserGroup, array $availableRights = [])
    {
        $this->backendUserGroup = $backendUserGroup;
        $this->availableRights  = $availableRights;
        $this->title = $this->backendUserGroup->getTitle();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSubGroups(): array
    {
        if (!$this->subGroups) {
            $this->subGroups = [];
            foreach ($this->backendUserGroup->getSubGroups() as $subGroup) {
                $this->subGroups[] = new self($subGroup, $this->availableRights);
            }
        }

        return $this->subGroups;
    }

    public function hasSubGroups(): bool
    {
        return count($this->getSubGroups()) > 0;
    }

    public function hasRight(string $group, string $field): bool
    {
        return count($this->getRightsInheritance($group, $field)) > 0;
    }

    public function getRightsInheritance(string $group, string $field): array
    {
        $rights = $this->getRights();
        $inheritance = [];
        if (isset($rights[$group])) {
            foreach ($rights[$group] as $groupTitle => $rightValues) {
                if (in_array($field, $rightValues)) {
                    $inheritance[] = $groupTitle;
                }
            }
        }

        return $inheritance;
    }

    private function getRights(): array
    {
        if (!$this->rights) {
            $this->rights = [
                'pagetypes' => [
                    $this->getTitle() => explode(',', $this->backendUserGroup->getPageTypes())
                ],
                'tables_select'  => [
                    $this->getTitle() => explode(',', $this->backendUserGroup->getTablesListening())
                ],
                'tables_modify' => [
                    $this->getTitle() => explode(',', $this->backendUserGroup->getTablesModify())
                ],
                'exclude' => [
                    $this->getTitle() => explode(',', $this->backendUserGroup->getAllowedExcludeFields())
                ],
                'explicitValues' => [
                    $this->getTitle() => explode(',', $this->backendUserGroup->getExplicitlyAllowAndDeny())
                ],
                'languages' => [
                    $this->getTitle() => explode(',', $this->backendUserGroup->getAllowedLanguages())
                ],
                'modListGroup' => [
                    $this->getTitle() => explode(',', $this->backendUserGroup->getModules())
                ]
            ];

            if ($this->hasSubGroups()) {
                foreach ($this->getSubGroups() as $subgroup) {
                    $mainGroupRights = $this->rights;
                    $subGroupRights = $subgroup->getRights();
                    $mergedGroupRights = array_merge_recursive($mainGroupRights, $subGroupRights);
                    $this->rights = $mergedGroupRights;
                }
            }
        }

        return $this->rights;
    }
}
