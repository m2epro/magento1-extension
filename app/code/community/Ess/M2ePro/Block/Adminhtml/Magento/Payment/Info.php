<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Magento_Payment_Info extends Mage_Payment_Block_Info
{
    protected $_order = null;

    //########################################

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('M2ePro/magento/order/payment/info.phtml');
    }

    //########################################

    protected function getAdditionalData($key = '')
    {
        $additionalData = Mage::helper('M2ePro')->unserialize($this->getInfo()->getAdditionalData());

        if ($key === '') {
            return $additionalData;
        }

        // backward compatibility with M2E Pro 3.1.5 or lower
        // ---------------------------------------
        $backwardCompatibleKeys = array(
            'payment_method'    => 'ebay_payment_method',
            'channel_order_id'  => 'ebay_order_id',
            'channel_final_fee' => 'ebay_final_value_fee',
            'transactions'      => 'external_transactions'
        );
        $backwardCompatibleKey = isset($backwardCompatibleKeys[$key]) ? $backwardCompatibleKeys[$key] : null;

        if (isset($additionalData[$backwardCompatibleKey])) {
            return $additionalData[$backwardCompatibleKey];
        }

        // ---------------------------------------

        return isset($additionalData[$key]) ? $additionalData[$key] : NULL;
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if ($this->_order === null) {
            // do not replace registry with our wrapper
            if ($this->hasOrder()) {
                $this->_order = $this->getOrder();
            } elseif (Mage::registry('current_order')) {
                $this->_order = Mage::registry('current_order');
            } elseif (Mage::registry('order')) {
                $this->_order = Mage::registry('order');
            } elseif (Mage::registry('current_invoice')) {
                $this->_order = Mage::registry('current_invoice')->getOrder();
            } elseif (Mage::registry('current_shipment')) {
                $this->_order = Mage::registry('current_shipment')->getOrder();
            } elseif (Mage::registry('current_creditmemo')) {
                $this->_order = Mage::registry('current_creditmemo')->getOrder();
            }
        }

        return $this->_order;
    }

    public function getPaymentMethod()
    {
        return (string)$this->getAdditionalData('payment_method');
    }

    public function getChannelOrderId()
    {
        return (string)$this->getAdditionalData('channel_order_id');
    }

    public function getTaxId()
    {
        return (string)$this->getAdditionalData('tax_id');
    }

    public function getChannelOrderUrl()
    {
        $url = '';

        switch ($this->getAdditionalData('component_mode')) {
            case Ess_M2ePro_Helper_Component_Ebay::NICK:
            case Ess_M2ePro_Helper_Component_Walmart::NICK:
                break;
            case Ess_M2ePro_Helper_Component_Amazon::NICK:
                if ($this->getOrder()) {
                    $url = Mage::helper('adminhtml')->getUrl(
                        'M2ePro/adminhtml_amazon_order/goToAmazon', array(
                        'magento_order_id' => $this->getOrder()->getId()
                        )
                    );
                }
                break;
        }

        return $url;
    }

    public function getChannelFinalFee()
    {
        return !$this->getIsSecureMode() ? (float)$this->getAdditionalData('channel_final_fee') : 0;
    }

    public function getCashOnDeliveryCost()
    {
        return !$this->getIsSecureMode() ? (float)$this->getAdditionalData('cash_on_delivery_cost') : 0;
    }

    public function getChannelTitle()
    {
        $component = $this->getAdditionalData('component_mode');
        return Mage::helper('M2ePro/Component_' . ucfirst($component))->getChannelTitle();
    }

    public function getTransactions()
    {
        $transactions = !$this->getIsSecureMode() ? $this->getAdditionalData('transactions') : array();

        return is_array($transactions) ? $transactions : array();
    }

    //########################################

    public function toPdf()
    {
        $this->setTemplate('M2ePro/magento/order/payment/pdf.phtml');
        return $this->toHtml();
    }

    protected function _toHtml()
    {
        // Start store emulation process
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation(
            Mage_Core_Model_App::ADMIN_STORE_ID, Mage_Core_Model_App_Area::AREA_ADMINHTML
        );

        $html = parent::_toHtml();

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        return $html;
    }

    //########################################
}
