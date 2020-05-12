<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Walmart_Order_Acknowledge extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'walmart/order/acknowledge';

    const MAX_ORDERS_COUNT = 50;

    //####################################

    protected function performActions()
    {
        $ordersForProcess = $this->getOrdersForProcess();
        if (empty($ordersForProcess)) {
            return;
        }

        foreach ($ordersForProcess as $order) {
            $order->getLog()->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);

            /** @var Ess_M2ePro_Model_Walmart_Order_Action_Handler_Acknowledge $actionHandler */
            $actionHandler = Mage::getModel('M2ePro/Walmart_Order_Action_Handler_Acknowledge');
            $actionHandler->setOrder($order);

            if ($actionHandler->isNeedProcess()) {
                $actionHandler->process();
            }

            $order->setData('is_tried_to_acknowledge', 1);
            $order->save();
        }
    }

    //####################################

    /**
     * @return Ess_M2ePro_Model_Order[]
     */
    protected function getOrdersForProcess()
    {
        /** @var Ess_M2ePro_Model_Resource_Order_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Order');
        $collection->addFieldToFilter('status', Ess_M2ePro_Model_Walmart_Order::STATUS_CREATED);
        $collection->addFieldToFilter('is_tried_to_acknowledge', 0);
        $collection->getSelect()->order('purchase_create_date ASC');
        $collection->getSelect()->limit(self::MAX_ORDERS_COUNT);

        return $collection->getItems();
    }

    //####################################
}
