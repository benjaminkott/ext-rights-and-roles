<?php
declare(strict_types = 1);

/*
 * This file is part of the package bk2k/rights-and-roles.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace BK2K\RightsAndRoles\ViewHelper;

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Class RightsViewHelper
 */
class RightsViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('group', 'object', 'Instance of RightsAndRolesMapping');
        $this->registerArgument('groupkey', 'string', 'groupkey');
        $this->registerArgument('groupvalue', 'string', 'groupvalue');
        $this->registerArgument('as', 'string', 'Name of variable to create', false, 'rightExists');
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     * @return mixed
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ) {
        $variableProvider = $renderingContext->getVariableProvider();
        $rightExists = false;

        $group = $arguments['group'];
        $groupkey = (string) $arguments['groupkey'];
        $groupvalue = (string) $arguments['groupvalue'];
        $as = (string) $arguments['as'];

        $variableProvider->remove('rightInhertance');
        $variableProvider->remove($as);

        if ($group->hasRight($groupkey, $groupvalue)) {
            $rightExists = true;
            $variableProvider->add('rightInhertance', implode('<br>', $group->getRightsInheritance($groupkey, $groupvalue)));
        }

        $variableProvider->add($as, $rightExists);
    }
}
