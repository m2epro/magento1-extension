<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Shipping
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'm2eproshipping';

    //########################################

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return bool|Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $shippingData = Mage::helper('M2ePro/Data_Global')->getValue('shipping_data');

        if (!$shippingData) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');
        $method = Mage::getModel('shipping/rate_result_method');

        $method->setCarrier($this->_code);
        $method->setMethod($this->_code);

        // eBay/Amazon Shipping
        $method->setCarrierTitle($shippingData['carrier_title']);
        $method->setMethodTitle($shippingData['shipping_method']);

        $method->setCost($shippingData['shipping_price']);
        $method->setPrice($shippingData['shipping_price']);

        $result->append($method);

        return $result;
    }

    /**
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return bool
     */
    public function checkAvailableShipCountries(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!Mage::helper('M2ePro/Data_Global')->getValue('shipping_data')) {
            return false;
        }

        return true;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array($this->_code => $this->getConfigData('name'));
    }

    /**
     * Check if carrier has shipping tracking option available
     *
     * @return boolean
     */
    public function isTrackingAvailable()
    {
        return false;
    }

    //########################################
}