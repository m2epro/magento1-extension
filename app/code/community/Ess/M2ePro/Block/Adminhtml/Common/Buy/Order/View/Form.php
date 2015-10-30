<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Common_Buy_Order_View_Form extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    public $shippingAddress = array();

    public $realMagentoOrderId = NULL;

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('buyOrderViewForm');
        $this->setTemplate('M2ePro/common/buy/order.phtml');
        // ---------------------------------------

        /** @var $order Ess_M2ePro_Model_Order */
        $this->order = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
    }

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
        /** @var $shippingAddress Ess_M2ePro_Model_Buy_Order_ShippingAddress */
        $shippingAddress = $this->order->getShippingAddress();

        $this->shippingAddress = $shippingAddress->getData();
        $this->shippingAddress['country_name'] = $shippingAddress->getCountryName();
        $this->shippingAddress['phone'] = $this->order->getChildObject()->getBillingAddress()->getData('phone');
        $this->shippingAddress['company'] = $this->order->getChildObject()->getBillingAddress()->getData('company');
        // ---------------------------------------

        $this->setChild('item', $this->getLayout()->createBlock('M2ePro/adminhtml_common_buy_order_view_item'));
        $this->setChild('item_edit', $this->getLayout()->createBlock('M2ePro/adminhtml_order_item_edit'));
        $this->setChild('log', $this->getLayout()->createBlock('M2ePro/adminhtml_order_view_log_grid'));

        return parent::_beforeToHtml();
    }

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
}