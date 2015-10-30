<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Order getParentObject()
 */
class Ess_M2ePro_Model_Buy_Order extends Ess_M2ePro_Model_Component_Child_Buy_Abstract
{
    private $subTotalPrice = NULL;

    private $grandTotalPrice = NULL;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Buy_Order');
    }

    //########################################

    public function getProxy()
    {
        return Mage::getModel('M2ePro/Buy_Order_Proxy', $this);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Buy_Account
     */
    public function getBuyAccount()
    {
        return $this->getParentObject()->getAccount()->getChildObject();
    }

    //########################################

    public function getBuyOrderId()
    {
        return $this->getData('buy_order_id');
    }

    public function getBuyerName()
    {
        return $this->getData('buyer_name');
    }

    public function getBuyerEmail()
    {
        return $this->getData('buyer_email');
    }

    public function getCurrency()
    {
        return $this->getData('currency');
    }

    public function getShippingMethod()
    {
        return $this->getData('shipping_method');
    }

    /**
     * @return float
     */
    public function getShippingPrice()
    {
        return (float)$this->getData('shipping_price');
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Order_ShippingAddress
     */
    public function getShippingAddress()
    {
        $address = json_decode($this->getData('shipping_address'), true);
        $address['country_code'] = 'US';

        return Mage::getModel('M2ePro/Buy_Order_ShippingAddress', $this->getParentObject())
            ->setData($address);
    }

    /**
     * @return Varien_Object
     */
    public function getBillingAddress()
    {
        return new Varien_Object((array)json_decode($this->getData('billing_address'), true));
    }

    /**
     * @return float
     */
    public function getPaidAmount()
    {
        return (float)$this->getData('paid_amount');
    }

    //########################################

    /**
     * @return float|null
     */
    public function getSubtotalPrice()
    {
        if (is_null($this->subTotalPrice)) {
            $this->subTotalPrice = $this->getResource()->getItemsTotal($this->getId());
        }

        return $this->subTotalPrice;
    }

    /**
     * @return float|null
     */
    public function getGrandTotalPrice()
    {
        if (is_null($this->grandTotalPrice)) {
            $this->grandTotalPrice = $this->getSubtotalPrice();
            $this->grandTotalPrice += round((float)$this->getData('shipping_price'), 2);
        }

        return $this->grandTotalPrice;
    }

    //########################################

    public function getStatusForMagentoOrder()
    {
        return $this->getBuyAccount()->getMagentoOrdersStatusProcessing();
    }

    //########################################

    /**
     * @return int|null
     */
    public function getAssociatedStoreId()
    {
        $storeId = NULL;

        $channelItems = $this->getParentObject()->getChannelItems();

        if (count($channelItems) == 0) {
            // 3rd party order
            // ---------------------------------------
            $storeId = $this->getBuyAccount()->getMagentoOrdersListingsOtherStoreId();
            // ---------------------------------------
        } else {
            // M2E order
            // ---------------------------------------
            if ($this->getBuyAccount()->isMagentoOrdersListingsStoreCustom()) {
                $storeId = $this->getBuyAccount()->getMagentoOrdersListingsStoreId();
            } else {
                $firstChannelItem = reset($channelItems);
                $storeId = $firstChannelItem->getStoreId();
            }
            // ---------------------------------------
        }

        if ($storeId == 0) {
            $storeId = Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId();
        }

        return $storeId;
    }

    //########################################

    /**
     * @return bool
     */
    public function canCreateMagentoOrder()
    {
        return true;
    }

    public function beforeCreateMagentoOrder()
    {

    }

    public function afterCreateMagentoOrder()
    {
        if ($this->getBuyAccount()->isMagentoOrdersCustomerNewNotifyWhenOrderCreated()) {
            if (method_exists($this->getParentObject()->getMagentoOrder(), 'queueNewOrderEmail')) {
                $this->getParentObject()->getMagentoOrder()->queueNewOrderEmail(false);
            } else {
                $this->getParentObject()->getMagentoOrder()->sendNewOrderEmail();
            }
        }
    }

    //########################################

    /**
     * @return bool
     */
    public function canCreateInvoice()
    {
        if (!$this->getBuyAccount()->isMagentoOrdersInvoiceEnabled()) {
            return false;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();
        if (is_null($magentoOrder)) {
            return false;
        }

        if ($magentoOrder->hasInvoices() || !$magentoOrder->canInvoice()) {
            return false;
        }

        return true;
    }

    /**
     * @return Mage_Sales_Model_Order_Invoice|null
     * @throws Exception
     */
    public function createInvoice()
    {
        if (!$this->canCreateInvoice()) {
            return NULL;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();

        // Create invoice
        // ---------------------------------------
        /** @var $invoiceBuilder Ess_M2ePro_Model_Magento_Order_Invoice */
        $invoiceBuilder = Mage::getModel('M2ePro/Magento_Order_Invoice');
        $invoiceBuilder->setMagentoOrder($magentoOrder);
        $invoiceBuilder->buildInvoice();
        // ---------------------------------------

        $invoice = $invoiceBuilder->getInvoice();

        if ($this->getBuyAccount()->isMagentoOrdersCustomerNewNotifyWhenInvoiceCreated()) {
            $invoice->sendEmail();
        }

        return $invoice;
    }

    //########################################

    /**
     * @return bool
     */
    public function canCreateShipment()
    {
        return false;
    }

    //########################################

    /**
     * @param array $trackingDetails
     * @return bool
     */
    public function canUpdateShippingStatus(array $trackingDetails = array())
    {
        return true;
    }

    /**
     * @param array $trackingDetails
     * @return bool
     */
    public function updateShippingStatus(array $trackingDetails = array())
    {
        if (empty($trackingDetails['tracking_number'])) {
            $this->getParentObject()->addErrorLog(
                'Shipping status for Rakuten.com Order cannot be updated. Reason: Tracking Information is Required.'
            );
            return false;
        }

        if (empty($trackingDetails['carrier_code'])) {
            $trackingDetails['carrier_code'] = 'custom';
        }

        if (empty($trackingDetails['carrier_title'])) {
            $trackingDetails['carrier_title'] = 'Other';
        }

        $trackingProvider = Mage::helper('M2ePro/Component_Buy')->getCarrierTitle(
            $trackingDetails['carrier_code'], $trackingDetails['carrier_title']
        );

        $shipDate = new DateTime('now', new DateTimeZone('UTC'));
        $shipDate->modify('-10 minutes');
        $shipDate = $shipDate->format('Y-m-d H:i:s');

        foreach ($this->getParentObject()->getItemsCollection()->getItems() as $item) {
            /** @var $item Ess_M2ePro_Model_Order_Item */

            $params = array(
                'buy_order_id'      => $this->getBuyOrderId(),
                'buy_order_item_id' => $item->getChildObject()->getBuyOrderItemId(),
                'qty'               => $item->getChildObject()->getQtyPurchased(),
                'tracking_type'     => $trackingProvider,
                'tracking_number'   => $trackingDetails['tracking_number'],
                'ship_date'         => $shipDate
            );

            $orderId     = $this->getParentObject()->getId();
            $action      = Ess_M2ePro_Model_Order_Change::ACTION_UPDATE_SHIPPING;
            $creatorType = Ess_M2ePro_Model_Order_Change::CREATOR_TYPE_OBSERVER;
            $component   = Ess_M2ePro_Helper_Component_Buy::NICK;

            Mage::getModel('M2ePro/Order_Change')->create($orderId, $action, $creatorType, $component, $params);
        }

        return true;
    }

    //########################################
}