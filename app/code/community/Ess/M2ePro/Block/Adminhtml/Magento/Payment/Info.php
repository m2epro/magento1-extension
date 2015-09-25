<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Magento_Payment_Info extends Mage_Payment_Block_Info
{
    private $order = NULL;

    // ########################################

    protected function _construct()
    {
        parent::_construct();
        $this->setData('area', 'adminhtml');
        $this->setTemplate('M2ePro/magento/order/payment/info.phtml');
    }

    // ########################################

    private function getAdditionalData($key = '')
    {
        $additionalData = @unserialize($this->getInfo()->getAdditionalData());

        if ($key === '') {
            return $additionalData;
        }

        // backward compatibility with M2E Pro 3.1.5 or lower
        // -----------
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
        // -----------

        return isset($additionalData[$key]) ? $additionalData[$key] : NULL;
    }

    public function getOrder()
    {
        if (is_null($this->order)) {
            // do not replace registry with our wrapper
            if ($this->hasOrder()) {
                $this->order = $this->getOrder();
            } elseif (Mage::registry('current_order')) {
                $this->order = Mage::registry('current_order');
            } elseif (Mage::registry('order')) {
                $this->order = Mage::registry('order');
            } elseif (Mage::registry('current_invoice')) {
                $this->order = Mage::registry('current_invoice')->getOrder();
            } elseif (Mage::registry('current_shipment')) {
                $this->order = Mage::registry('current_shipment')->getOrder();
            }
        }

        return $this->order;
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
            case Ess_M2ePro_Helper_Component_Buy::NICK:
                break;
            case Ess_M2ePro_Helper_Component_Amazon::NICK:
                if ($this->getOrder()) {
                    $url = Mage::helper('adminhtml')->getUrl('M2ePro/adminhtml_common_amazon_order/goToAmazon', array(
                        'magento_order_id' => $this->getOrder()->getId()
                    ));
                }
                break;
        }

        return $url;
    }

    public function getChannelFinalFee()
    {
        return !$this->getIsSecureMode() ? (float)$this->getAdditionalData('channel_final_fee') : 0;
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

    // ########################################

    public function toPdf()
    {
        $this->setTemplate('M2ePro/magento/order/payment/pdf.phtml');
        return $this->toHtml();
    }

    // ########################################
}