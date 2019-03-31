<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Order_MerchantFulfillment_Magento_Shipment
    extends Mage_Adminhtml_Block_Template
{
    /** @var Ess_M2ePro_Model_Order $order */
    protected $order = NULL;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonOrderMerchantFulfillmentMagentoShipment');
        // ---------------------------------------

        $this->setTemplate('M2ePro/amazon/order/merchant_fulfillment/magento/shipment.phtml');
    }

    //########################################

    public function setOrder(Ess_M2ePro_Model_Order $order)
    {
        $this->order = $order;
        return $this;
    }

    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Order
     */
    public function getAmazonOrder()
    {
        return $this->order->getChildObject();
    }

    //########################################

    public function canShowNotificationPopup()
    {
        $registry = Mage::getModel('M2ePro/Registry');
        $registry->load('/amazon/order/merchant_fulfillment/disable_notification_popup/', 'key');
        return !$registry->getValue();
    }

    //########################################
}