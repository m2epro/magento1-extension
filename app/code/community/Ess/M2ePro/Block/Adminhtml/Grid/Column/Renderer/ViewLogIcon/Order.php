<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Grid_Column_Renderer_ViewLogIcon_Order extends
    Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    //########################################

    public function render(Varien_Object $row)
    {
        $orderId = (int)$row->getId();

        $orderLogsCollection = Mage::getModel('M2ePro/Order_Log')->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->setOrder('id', 'DESC');
        $orderLogsCollection->getSelect()
            ->limit(Ess_M2ePro_Block_Adminhtml_Log_Grid_LastActions::ACTIONS_COUNT);

        if (!$orderLogsCollection->getSize()) {
            return '';
        }

        // ---------------------------------------

        $lastActions = $this->getLayout()->createBlock('M2ePro/adminhtml_order_log_grid_lastActions')
            ->setData(
                array(
                    'entity_id' => $orderId,
                    'logs'      => $orderLogsCollection->getItems(),
                    'view_help_handler' => 'OrderObj.viewOrderHelp',
                    'hide_help_handler' => 'OrderObj.hideOrderHelp',
                    )
            );

        return $lastActions->toHtml();
    }

    //########################################
}
