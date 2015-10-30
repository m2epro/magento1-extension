<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Order_View_Form extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    public $shippingAddress = array();

    public $ebayWarehouseAddress = array();

    public $globalShippingServiceDetails = array();

    public $realMagentoOrderId = null;

    /** @var $order Ess_M2ePro_Model_Order */
    public $order = null;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayOrderViewForm');
        $this->setTemplate('M2ePro/ebay/order.phtml');
        // ---------------------------------------

        $this->order = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
    }

    //########################################

    protected function _beforeToHtml()
    {
        // Magento order data
        // ---------------------------------------
        $this->realMagentoOrderId = NULL;

        $magentoOrder = $this->order->getMagentoOrder();
        if (!is_null($magentoOrder)) {
            $this->realMagentoOrderId = $magentoOrder->getRealOrderId();
        }
        // ---------------------------------------

        // ---------------------------------------
        if (!is_null($magentoOrder) && $magentoOrder->hasShipments()) {
            $url = $this->getUrl('*/adminhtml_order/resubmitShippingInfo', array('id' => $this->order->getId()));
            $data = array(
                'class'   => '',
                'label'   => Mage::helper('M2ePro')->__('Resend Shipping Information'),
                'onclick' => 'setLocation(\''.$url.'\');',
            );
            $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
            $this->setChild('resubmit_shipping_info', $buttonBlock);
        }
        // ---------------------------------------

        // Shipping data
        // ---------------------------------------
        /** @var $shippingAddress Ess_M2ePro_Model_Ebay_Order_ShippingAddress */
        $shippingAddress = $this->order->getShippingAddress();

        $this->shippingAddress = $shippingAddress->getData();
        $this->shippingAddress['country_name'] = $shippingAddress->getCountryName();
        // ---------------------------------------

        // Global Shipping data
        // ---------------------------------------
        $globalShippingDetails = $this->order->getChildObject()->getGlobalShippingDetails();
        if (!empty($globalShippingDetails)) {
            $this->ebayWarehouseAddress = $globalShippingDetails['warehouse_address'];
            $this->globalShippingServiceDetails = $globalShippingDetails['service_details'];
        }
        // ---------------------------------------

        $this->setChild('item', $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_order_view_item'));
        $this->setChild('item_edit', $this->getLayout()->createBlock('M2ePro/adminhtml_order_item_edit'));
        $this->setChild('log', $this->getLayout()->createBlock('M2ePro/adminhtml_order_view_log_grid'));
        $this->setChild('external_transaction', $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_order_view_externalTransaction'
        ));

        return parent::_beforeToHtml();
    }

    //########################################

    private function getStore()
    {
        if (is_null($this->order->getData('store_id'))) {
            return null;
        }

        try {
            $store = Mage::app()->getStore($this->order->getData('store_id'));
        } catch (Exception $e) {
            return null;
        }

        return $store;
    }

    public function isCurrencyAllowed()
    {
        $store = $this->getStore();

        if (is_null($store)) {
            return true;
        }

        /** @var $currencyHelper Ess_M2ePro_Model_Currency */
        $currencyHelper = Mage::getSingleton('M2ePro/Currency');

        return $currencyHelper->isAllowed($this->order->getChildObject()->getCurrency(), $store);
    }

    public function hasCurrencyConversionRate()
    {
        $store = $this->getStore();

        if (is_null($store)) {
            return true;
        }

        /** @var $currencyHelper Ess_M2ePro_Model_Currency */
        $currencyHelper = Mage::getSingleton('M2ePro/Currency');

        return $currencyHelper->getConvertRateFromBase($this->order->getChildObject()->getCurrency(), $store) != 0;
    }

    //########################################

    public function getSubtotalPrice()
    {
        return $this->order->getChildObject()->getSubtotalPrice();
    }

    public function getShippingPrice()
    {
        $shippingPrice = $this->order->getChildObject()->getShippingPrice();
        if (!$this->order->getChildObject()->isVatTax()) {
            return $shippingPrice;
        }

        $shippingPrice -= Mage::getSingleton('tax/calculation')->calcTaxAmount(
            $shippingPrice, $this->order->getChildObject()->getTaxRate(), true, false
        );

        return $shippingPrice;
    }

    public function getTaxAmount()
    {
        $taxAmount = $this->order->getChildObject()->getTaxAmount();
        if (!$this->order->getChildObject()->isVatTax()) {
            return $taxAmount;
        }

        $shippingPrice = $this->order->getChildObject()->getShippingPrice();
        $shippingTaxAmount = Mage::getSingleton('tax/calculation')->calcTaxAmount(
            $shippingPrice, $this->order->getChildObject()->getTaxRate(), true, false
        );

        return $taxAmount + $shippingTaxAmount;
    }

    //########################################
}