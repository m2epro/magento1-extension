<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Listing_Product as Listing_Product;

class Ess_M2ePro_Block_Adminhtml_Grid_Column_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    //########################################

    public function render(Varien_Object $row)
    {
        $actions = $this->getColumn()->getActions();
        if (empty($actions) || !is_array($actions)) {
            return '&nbsp;';
        }

        foreach ($actions as $columnName => $value) {
            if (array_key_exists('only_remap_product', $value) && $value['only_remap_product']) {
                $additionalData = (array)Mage::helper('M2ePro')->jsonDecode($row->getData('additional_data'));

                if ($this->isNeedShowRemapActionForProduct($row)) {
                    continue;
                }

                if (!isset($additionalData[Listing_Product::MOVING_LISTING_OTHER_SOURCE_KEY])) {
                    unset($actions[$columnName]);
                }
            }
        }

        if (sizeof($actions) == 1 && !$this->getColumn()->getNoLink()) {
            foreach ($actions as $action) {
                if (is_array($action)) {
                    return $this->_toLinkHtml($action, $row);
                }
            }
        }

        $itemParam = $row->getId();
        $field = $this->getColumn()->getData('field');
        $groupOrder = $this->getColumn()->getGroupOrder();

        if (!empty($field)) {
            $itemParam = $row->getData($field);
        }

        if (!empty($groupOrder) && is_array($groupOrder)) {
            $actions = $this->sortActionsByGroupsOrder($groupOrder, $actions);
        }

        return <<<HTML
<select class="action-select" onchange="ActionColumnObj.callAction(this, '{$itemParam}');">
    <option value=""></option>
    {$this->renderOptions($actions, $row)}
</select>
HTML;
    }

    protected function sortActionsByGroupsOrder(array $groupOrder, array $actions)
    {
        $sorted = array();

        foreach ($groupOrder as $groupId => $groupLabel) {
            $sorted[$groupId] = array(
                'label'   => $groupLabel,
                'actions' => array()
            );

            foreach ($actions as $actionId => $actionData) {
                if (isset($actionData['group']) && ($actionData['group'] == $groupId)) {
                    $sorted[$groupId]['actions'][$actionId] = $actionData;
                    unset($actions[$actionId]);
                }
            }
        }

        return array_merge($sorted, $actions);
    }

    protected function renderOptions(array $actions, Varien_Object $row)
    {
        $outHtml = '';
        $notGroupedOptions = '';

        foreach ($actions as $groupId => $group) {
            if (isset($group['label']) && empty($group['actions'])) {
                continue;
            }

            if (!isset($group['label']) && !empty($group)) {
                $notGroupedOptions .= $this->_toOptionHtml($group, $row);
                continue;
            }

            $outHtml .= "<optgroup label='{$group['label']}'>";

            foreach ($group['actions'] as $actionId => $actionData) {
                $outHtml .= $this->_toOptionHtml($actionData, $row);
            }

            $outHtml .= "</optgroup>";
        }

        return $outHtml . $notGroupedOptions;
    }

    //########################################

    /**
     * In some causes default Magento logic in foreach method is not working.
     * In result variables located in $action['url']['params'] will not we replaced.
     *
     * @param array $action
     * @param string $actionCaption
     * @param Varien_Object $row
     * @return Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
     */
    protected function _transformActionData(&$action, &$actionCaption, Varien_Object $row)
    {
        if (!empty($action['url']['params']) && is_array($action['url']['params'])) {
            foreach ($action['url']['params'] as $paramKey => $paramValue) {
                if (strpos($paramValue, '$') === 0) {
                    $paramValue = str_replace('$', '', $paramValue);
                    $action['url']['params'][$paramKey] = $row->getData($paramValue);
                }
            }
        }

        return parent::_transformActionData($action, $actionCaption, $row);
    }

    //########################################

    private function isNeedShowRemapActionForProduct(Varien_Object $row)
    {
        $component = $row->getData('component_mode');
        if (
            $component !== Ess_M2ePro_Helper_Component_Amazon::NICK
            && $component !== Ess_M2ePro_Helper_Component_Walmart::NICK
        ) {
            return false;
        }

        if (
            $row->getData('is_general_id_owner')
            == Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_YES
        ) {
            return false;
        }

        if (
            $row->hasData('is_variation_product')
            && $row->getData('is_variation_product') != 0
        ) {
            return false;
        }

        return true;
    }
}
