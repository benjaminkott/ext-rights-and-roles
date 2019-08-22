<?php
declare(strict_types = 1);

/*
 * This file is part of the package bk2k/rights-and-roles.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace BK2K\RightsAndRoles\Utility;

use TYPO3\CMS\Backend\Configuration\TranslationConfigurationProvider;
use TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems;
use TYPO3\CMS\Backend\Form\Utility\FormEngineUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class TcaSelectItemsInjectHelper.
 */
class TcaSelectItemsInjectHelper extends TcaSelectItems
{
    public function getSpecialFieldsConfiguration(): array
    {
        $fields = [
            'pagetypes'         => $GLOBALS['TCA']['be_groups']['columns']['pagetypes_select'],
            'tables_select'     => $GLOBALS['TCA']['be_groups']['columns']['tables_select'],
            'tables_modify'     => $GLOBALS['TCA']['be_groups']['columns']['tables_modify'],
            'exclude'           => $GLOBALS['TCA']['be_groups']['columns']['non_exclude_fields'],
            'explicitValues'    => $GLOBALS['TCA']['be_groups']['columns']['explicit_allowdeny'],
            'languages'         => $GLOBALS['TCA']['be_groups']['columns']['allowed_languages'],
            'modListGroup'      => $GLOBALS['TCA']['be_groups']['columns']['groupMods'],
        ];

        foreach ($fields as $field => $fieldConfiguration) {
            $result[$field] = $this->getSpecialConfiguration($field, $fieldConfiguration);
        }

        return $result;
    }

    /**
     * @param string $fieldName
     * @param array  $fieldConfiguration
     * @return array
     */
    private function getSpecialConfiguration(string $fieldName, array $fieldConfiguration): array
    {
        $result = [
            'processedTca' => [
                'columns' => [
                    $fieldName => $fieldConfiguration
                ]
            ]
        ];
        if ($fieldName === 'languages') {
            $translationProvider = GeneralUtility::makeInstance(TranslationConfigurationProvider::class);
            $languages = $translationProvider->getSystemLanguages();
            $result['systemLanguageRows'] = $languages;
        }

        $result = $this->addItemsFromSpecial($result, $fieldName, []);

        if (count($result) > 0) {
            foreach ($result as $id => $item) {
                $itemConfigured = [];
                foreach ([0 => 'label', 1 => 'value', 2 => 'icon'] as $itemCounter => $itemFieldName) {
                    if (isset($item[$itemCounter])) {
                        $itemConfigured[$itemFieldName] = $item[$itemCounter];
                    }
                }
                if (isset($itemConfigured['label'])) {
                    if (strpos($itemConfigured['label'], 'LLL:') !== false) {
                        $itemConfigured['label'] = LocalizationUtility::translate($itemConfigured['label'], null);
                    }
                }
                if (isset($itemConfigured['icon'])) {
                    $itemConfigured['icon'] = FormEngineUtility::getIconHtml($itemConfigured['icon']);
                }
                $result[$id] = $itemConfigured;
            }
        }

        return $result;
    }
}
