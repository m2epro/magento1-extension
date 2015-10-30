<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Grid_Column_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    //########################################

    public function render(Varien_Object $row)
    {
        $actions = $this->getColumn()->getActions();
        if (empty($actions) || !is_array($actions) ) {
            return '&nbsp;';
        }

        if (sizeof($actions)==1 && !$this->getColumn()->getNoLink()) {
            foreach ($actions as $action) {
                if (is_array($action)) {
                    return $this->_toLinkHtml($action, $row);
                }
            }
        }

        $itemId     = $row->getId();
        $field      = $this->getColumn()->getData('field');
        $groupOrder = $this->getColumn()->getGroupOrder();

        if (!empty($field)) {
            $itemId = $row->getData($field);
        }

        if (!empty($groupOrder) && is_array($groupOrder)) {
            $actions = $this->sortActionsByGroupsOrder($groupOrder, $actions);
        }

        return ' <select class="action-select" onchange="ActionColumnObj.callAction(this, ' . $itemId . ');">'
               . '<option value=""></option>'
               . $this->renderOptions($actions, $row)
               . '</select>';
    }

    protected function sortActionsByGroupsOrder(array $groupOrder, array $actions)
    {
        $sorted = array();

        foreach ($groupOrder as $groupId => $groupLabel) {

            $sorted[$groupId] = array(
                'label' => $groupLabel,
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
        $outHtml           = '';
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
}