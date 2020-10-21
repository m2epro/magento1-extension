<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Order_Shipment_Track
{
    /** @var $shipment Mage_Sales_Model_Order */
    protected $_magentoOrder = null;

    protected $_supportedCarriers = array();

    protected $_trackingDetails = array();

    protected $_tracks = array();

    //########################################

    /**
     * @param Mage_Sales_Model_Order $magentoOrder
     * @return $this
     */
    public function setMagentoOrder(Mage_Sales_Model_Order $magentoOrder)
    {
        $this->_magentoOrder = $magentoOrder;
        return $this;
    }

    //########################################

    /**
     * @param array $trackingDetails
     * @return $this
     */
    public function setTrackingDetails(array $trackingDetails)
    {
        $this->_trackingDetails = $trackingDetails;
        return $this;
    }

    //########################################

    /**
     * @param array $supportedCarriers
     * @return $this
     */
    public function setSupportedCarriers(array $supportedCarriers)
    {
        $this->_supportedCarriers = $supportedCarriers;
        return $this;
    }

    //########################################

    public function getTracks()
    {
        return $this->_tracks;
    }

    //########################################

    public function buildTracks()
    {
        $this->prepareTracks();
    }

    //########################################

    protected function prepareTracks()
    {
        $trackingDetails = $this->getFilteredTrackingDetails();
        if (empty($trackingDetails)) {
            return null;
        }

        // Skip shipment observer
        // ---------------------------------------
        Mage::helper('M2ePro/Data_Global')->unsetValue('skip_shipment_observer');
        Mage::helper('M2ePro/Data_Global')->setValue('skip_shipment_observer', true);
        // ---------------------------------------

        /** @var $shipment Mage_Sales_Model_Order_Shipment */
        $shipment = $this->_magentoOrder->getShipmentsCollection()->getFirstItem();

        foreach ($trackingDetails as $trackingDetail) {
            /** @var $track Mage_Sales_Model_Order_Shipment_Track */
            $track = Mage::getModel('sales/order_shipment_track');
            $track->setNumber($trackingDetail['number'])
                  ->setTitle($trackingDetail['title'])
                  ->setCarrierCode($this->getCarrierCode($trackingDetail['title']));
            $shipment->addTrack($track)->save();

            $this->_tracks[] = $track;
        }
    }

    // ---------------------------------------

    protected function getFilteredTrackingDetails()
    {
        if ($this->_magentoOrder->getTracksCollection()->getSize() <= 0) {
            return $this->_trackingDetails;
        }

        // Filter exist tracks
        // ---------------------------------------
        foreach ($this->_magentoOrder->getTracksCollection() as $track) {
            foreach ($this->_trackingDetails as $key => $trackingDetail) {
                if (strtolower($track->getData('number')) == strtolower($trackingDetail['number'])) {
                    unset($this->_trackingDetails[$key]);
                }
            }
        }

        // ---------------------------------------

        return $this->_trackingDetails;
    }

    // ---------------------------------------

    protected function getCarrierCode($title)
    {
        $carrierCode = strtolower($title);

        return isset($this->_supportedCarriers[$carrierCode]) ? $carrierCode : 'custom';
    }

    //########################################
}
