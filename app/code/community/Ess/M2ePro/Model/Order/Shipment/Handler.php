<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * Handles shipments, created by seller in admin panel
 */
abstract class Ess_M2ePro_Model_Order_Shipment_Handler
{
    const HANDLE_RESULT_FAILED    = -1;
    const HANDLE_RESULT_SKIPPED   = 0;
    const HANDLE_RESULT_SUCCEEDED = 1;

    const CUSTOM_CARRIER_CODE = 'custom';

    //########################################

    abstract public function handle(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Shipment $shipment);

    //########################################

    public static function factory($component)
    {
        $handler = null;

        switch ($component) {
            case Ess_M2ePro_Helper_Component_Amazon::NICK:
                $handler = Mage::getModel('M2ePro/Amazon_Order_Shipment_Handler');
                break;
            case Ess_M2ePro_Helper_Component_Ebay::NICK:
                $handler = Mage::getModel('M2ePro/Ebay_Order_Shipment_Handler');
                break;
            case Ess_M2ePro_Helper_Component_Walmart::NICK:
                $handler = Mage::getModel('M2ePro/Walmart_Order_Shipment_Handler');
                break;
        }

        if (!$handler) {
            throw new Ess_M2ePro_Model_Exception_Logic('Shipment handler not found.');
        }

        return $handler;
    }

    //########################################

    protected function getTrackingDetails(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Shipment $shipment)
    {
        $track = $shipment->getTracksCollection()->getLastItem();
        $trackingDetails = array();

        $number = trim($track->getData('number'));

        if (!empty($number)) {
            $carrierCode = $carrierTitle = trim($track->getData('carrier_code'));

            $carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($carrierCode, $order->getStoreId());
            $carrier && $carrierTitle = $carrier->getConfigData('title');

            $trackingDetails = array(
                'carrier_code'    => $carrierCode,
                'carrier_title'   => $carrierTitle,
                'shipping_method' => trim($track->getData('title')),
                'tracking_number' => (string)$number
            );
        }

        return $trackingDetails;
    }

    //########################################
}