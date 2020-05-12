<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Order
{
    /** @var $_quote Mage_Sales_Model_Quote */
    protected $_quote = null;

    /** @var $_order Mage_Sales_Model_Order */
    protected $_order = null;

    protected $_additionalData = array();

    //########################################

    public function __construct(Mage_Sales_Model_Quote $quote)
    {
        $this->_quote = $quote;
    }

    //########################################

    public function setAdditionalData($additionalData)
    {
        $this->_additionalData = $additionalData;
        return $this;
    }

    //########################################

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    //########################################

    public function buildOrder()
    {
        $this->createOrder();
    }

    protected function createOrder()
    {
        try {
            $this->_order = $this->placeOrder();
        } catch (Exception $e) {
            // Remove ordered items from customer cart
            // ---------------------------------------
            $this->_quote->setIsActive(false)->save();
            // ---------------------------------------
            throw $e;
        }

        // Remove ordered items from customer cart
        // ---------------------------------------
        $this->_quote->setIsActive(false)->save();
        // ---------------------------------------
    }

    protected function placeOrder()
    {
        /** @var $service Mage_Sales_Model_Service_Quote */
        $service = Mage::getModel('sales/service_quote', $this->_quote);
        $service->setOrderData($this->_additionalData);
        $service->submitAll();

        return $service->getOrder();
    }

    //########################################
}
