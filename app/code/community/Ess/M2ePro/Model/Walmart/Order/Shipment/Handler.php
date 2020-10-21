<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Order_Shipment_Handler extends Ess_M2ePro_Model_Order_Shipment_Handler
{
    //########################################

    protected function getComponentMode()
    {
        return Ess_M2ePro_Helper_Component_Walmart::NICK;
    }

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return array
     */
    protected function getTrackingDetails(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Shipment $shipment)
    {
        return array_merge(
            parent::getTrackingDetails($order, $shipment),
            array('fulfillment_date' => $shipment->getCreatedAt())
        );
    }

    //########################################
}
