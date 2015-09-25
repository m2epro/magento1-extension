<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Shipment_Track extends Ess_M2ePro_Model_Observer_Abstract
{
    //####################################

    public function process()
    {
        try {

            if (Mage::helper('M2ePro/Data_Global')->getValue('skip_shipment_observer')) {
                return;
            }

            /** @var $track Mage_Sales_Model_Order_Shipment_Track */

            $track = $this->getEvent()->getTrack();

            $shipment = $track->getShipment();
            $magentoOrderId = $shipment->getOrderId();

            try {
                /** @var $order Ess_M2ePro_Model_Order */
                $order = Mage::helper('M2ePro/Component')
                    ->getUnknownObject('Order', $magentoOrderId, 'magento_order_id');
            } catch (Exception $e) {
                return;
            }

            if (is_null($order)) {
                return;
            }

            if (!in_array($order->getComponentMode(), Mage::helper('M2ePro/Component')->getActiveComponents())) {
                return;
            }

            Mage::getSingleton('M2ePro/Order_Log_Manager')
                ->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);

            // -------------
            /** @var $shipmentHandler Ess_M2ePro_Model_Order_Shipment_Handler */
            $shipmentHandler = Mage::getModel('M2ePro/Order_Shipment_Handler')->factory($order->getComponentMode());
            $shipmentHandler->handle($order, $shipment);
            // -------------

        } catch (Exception $exception) {

            Mage::helper('M2ePro/Module_Exception')->process($exception);

        }
    }

    //####################################
}