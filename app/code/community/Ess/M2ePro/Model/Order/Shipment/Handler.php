<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * Handles shipments, created by seller in admin panel
 */
class Ess_M2ePro_Model_Order_Shipment_Handler
{
    const HANDLE_RESULT_FAILED    = -1;
    const HANDLE_RESULT_SKIPPED   = 0;
    const HANDLE_RESULT_SUCCEEDED = 1;

    // ########################################

    public static function factory($component)
    {
        $handler = null;

        switch ($component) {
            case Ess_M2ePro_Helper_Component_Buy::NICK:
                $handler = Mage::getModel('M2ePro/Order_Shipment_Handler');
                break;
            case Ess_M2ePro_Helper_Component_Amazon::NICK:
                $handler = Mage::getModel('M2ePro/Amazon_Order_Shipment_Handler');
                break;
            case Ess_M2ePro_Helper_Component_Ebay::NICK:
                $handler = Mage::getModel('M2ePro/Ebay_Order_Shipment_Handler');
                break;
        }

        if (!$handler) {
            throw new Ess_M2ePro_Model_Exception_Logic('Shipment handler not found.');
        }

        return $handler;
    }

    public function handle(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Shipment $shipment)
    {
        $trackingDetails = $this->getTrackingDetails($shipment);

        if (!$order->getChildObject()->canUpdateShippingStatus($trackingDetails)) {
            return self::HANDLE_RESULT_SKIPPED;
        }

        return $order->getChildObject()->updateShippingStatus($trackingDetails)
            ? self::HANDLE_RESULT_SUCCEEDED
            : self::HANDLE_RESULT_FAILED;
    }

    protected function getTrackingDetails(Mage_Sales_Model_Order_Shipment $shipment)
    {
        $track = $shipment->getTracksCollection()->getLastItem();
        $trackingDetails = array();

        $number = trim($track->getData('number'));

        if (!empty($number)) {
            $carrierCode = trim($track->getData('carrier_code'));

            if (strtolower($carrierCode) == 'dhlint') {
                $carrierCode = 'dhl';
            }

            $trackingDetails = array(
                'carrier_title'   => trim($track->getData('title')),
                'carrier_code'    => $carrierCode,
                'tracking_number' => (string)$number
            );
        }

        return $trackingDetails;
    }

    // ########################################
}