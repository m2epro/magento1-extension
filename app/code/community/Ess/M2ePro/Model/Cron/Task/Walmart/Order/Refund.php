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

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function performActions()
    {
        $this->deleteNotActualChanges();

        $ordersChangesForProcess = $this->getOrdersChangesForProcess();
        if (empty($ordersChangesForProcess)) {
            return;
        }

        Mage::getResourceModel('M2ePro/Order_Change')->incrementAttemptCount(array_keys($ordersChangesForProcess));

        foreach ($ordersChangesForProcess as $orderChange) {
            /** @var Ess_M2ePro_Model_Order $order */
            $order = Mage::helper('M2ePro/Component_Walmart')->getObject('Order', $orderChange->getOrderId());
            $order->getLog()->setInitiator($orderChange->getCreatorType());

            /** @var Ess_M2ePro_Model_Walmart_Order_Action_Handler_Refund $actionHandler */
            $actionHandler = Mage::getModel('M2ePro/Walmart_Order_Action_Handler_Refund');
            $actionHandler->setOrder($order);
            $actionHandler->setOrderChange($orderChange);

            if ($actionHandler->isNeedProcess()) {
                $actionHandler->process();
            } else {
                $orderChange->deleteInstance();
            }
        }
    }

    //####################################

    /**
     * @return Ess_M2ePro_Model_Order_Change[]
     */
    protected function getOrdersChangesForProcess()
    {
        /** @var Ess_M2ePro_Model_Resource_Order_Change_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Order_Change_Collection');
        $collection->addProcessingAttemptDateFilter();
        $collection->addFieldToFilter('component', Ess_M2ePro_Helper_Component_Walmart::NICK);
        $collection->addFieldToFilter('action', Ess_M2ePro_Model_Order_Change::ACTION_REFUND);
        $collection->getSelect()->limit(self::MAX_ORDERS_CHANGES_COUNT);
        $collection->getSelect()->group('order_id');

        return $collection->getItems();
    }

    protected function deleteNotActualChanges()
    {
        Mage::getResourceModel('M2ePro/Order_Change')
            ->deleteByProcessingAttemptCount(
                Ess_M2ePro_Model_Order_Change::MAX_ALLOWED_PROCESSING_ATTEMPTS,
                Ess_M2ePro_Helper_Component_Walmart::NICK
            );
    }

    //####################################
}
