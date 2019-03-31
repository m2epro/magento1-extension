<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Walmart_Order_Item as OrderItem;

class Ess_M2ePro_Model_Walmart_Order_Helper
{
    //########################################

    /**
     * @param array $itemsStatuses
     * @return int
     */
    public function getOrderStatus(array $itemsStatuses)
    {
        $isStatusSame         = count(array_unique($itemsStatuses)) == 1;
        $hasAcknowledgedItems = in_array(OrderItem::STATUS_ACKNOWLEDGED, $itemsStatuses);
        $hasShippedItems      = in_array(OrderItem::STATUS_SHIPPED, $itemsStatuses);

        if ($hasAcknowledgedItems && $hasShippedItems) {
            return Ess_M2ePro_Model_Walmart_Order::STATUS_SHIPPED_PARTIALLY;
        }

        if (!$isStatusSame && $hasShippedItems) {
            return Ess_M2ePro_Model_Walmart_Order::STATUS_SHIPPED;
        }

        if (!$isStatusSame) {
            return Ess_M2ePro_Model_Walmart_Order::STATUS_UNSHIPPED;
        }

        $resultStatus = NULL;

        switch (array_shift($itemsStatuses)) {

            case Ess_M2ePro_Model_Walmart_Order_Item::STATUS_CREATED:
                $resultStatus = Ess_M2ePro_Model_Walmart_Order::STATUS_CREATED;
                break;

            case Ess_M2ePro_Model_Walmart_Order_Item::STATUS_ACKNOWLEDGED:
                $resultStatus = Ess_M2ePro_Model_Walmart_Order::STATUS_UNSHIPPED;
                break;

            case Ess_M2ePro_Model_Walmart_Order_Item::STATUS_SHIPPED:
                $resultStatus = Ess_M2ePro_Model_Walmart_Order::STATUS_SHIPPED;
                break;

            case Ess_M2ePro_Model_Walmart_Order_Item::STATUS_CANCELLED:
                $resultStatus = Ess_M2ePro_Model_Walmart_Order::STATUS_CANCELED;
                break;
        }

        return $resultStatus;
    }

    //########################################
}