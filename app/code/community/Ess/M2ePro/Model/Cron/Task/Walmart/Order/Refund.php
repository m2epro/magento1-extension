<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Walmart_Order_Refund extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'walmart/order/refund';

    const MAX_ORDERS_CHANGES_COUNT = 50;

    //####################################

    protected function performActions()
    {
        $ordersChangesForProcess = $this->getOrdersChangesForProcess();
        if (empty($ordersChangesForProcess)) {
            return;
        }

        foreach ($ordersChangesForProcess as $orderChange) {
            /** @var Ess_M2ePro_Model_Order $order */
            $order = Mage::helper('M2ePro/Component_Walmart')->getObject('Order', $orderChange->getOrderId());

            $actionHandler = Mage::getModel('M2ePro/Walmart_Order_Action_Handler_Refund');
            $actionHandler->setOrder($order);
            $actionHandler->setParams($orderChange->getParams());

            if ($actionHandler->isNeedProcess()) {
                $actionHandler->process();
            }

            $orderChange->deleteInstance();
        }
    }

    //####################################

    /**
     * @return Ess_M2ePro_Model_Order_Change[]
     */
    private function getOrdersChangesForProcess()
    {
        /** @var Ess_M2ePro_Model_Mysql4_Order_Change_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Order_Change_Collection');
        $collection->addFieldToFilter('component', Ess_M2ePro_Helper_Component_Walmart::NICK);
        $collection->addFieldToFilter('action', Ess_M2ePro_Model_Order_Change::ACTION_REFUND);
        $collection->getSelect()->limit(self::MAX_ORDERS_CHANGES_COUNT);
        $collection->getSelect()->group('order_id');

        return $collection->getItems();
    }

    //####################################
}