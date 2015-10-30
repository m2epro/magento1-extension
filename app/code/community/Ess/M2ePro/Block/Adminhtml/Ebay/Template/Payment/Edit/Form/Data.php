<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Payment_Edit_Form_Data extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayTemplatePaymentEditFormData');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/template/payment/form/data.phtml');
    }

    //########################################

    public function isCustom()
    {
        if (isset($this->_data['is_custom'])) {
            return (bool)$this->_data['is_custom'];
        }

        return false;
    }

    public function getTitle()
    {
        if ($this->isCustom()) {
            return isset($this->_data['custom_title']) ? $this->_data['custom_title'] : '';
        }

        $template = Mage::helper('M2ePro/Data_Global')->getValue('ebay_template_payment');

        if (is_null($template)) {
            return '';
        }

        return $template->getTitle();
    }

    //########################################

    public function getFormData()
    {
        $template = Mage::helper('M2ePro/Data_Global')->getValue('ebay_template_payment');

        if (is_null($template)) {
            return array();
        }

        $data = $template->getData();
        $data['services'] = Mage::getModel('M2ePro/Ebay_Template_Payment_Service')
            ->getCollection()
                ->addFieldToFilter('template_payment_id', $template->getId())
                ->getColumnValues('code_name');

        return $data;
    }

    public function getDefault()
    {
        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            $default = Mage::getSingleton('M2ePro/Ebay_Template_Payment')->getDefaultSettingsSimpleMode();
        } else {
            $default = Mage::getSingleton('M2ePro/Ebay_Template_Payment')->getDefaultSettingsAdvancedMode();
        }

        // populate payment fields with the data from magento configuration
        // ---------------------------------------
        $store = Mage::helper('M2ePro/Data_Global')->getValue('ebay_store');

        $payPalConfig = Mage::getModel('paypal/config');
        $payPalConfig->setStoreId($store->getId());

        if ($payPalConfig->business_account) {
            $default['pay_pal_mode'] = 1;
            $default['pay_pal_email_address'] = $payPalConfig->business_account;
        }
        // ---------------------------------------

        return $default;
    }

    public function getMarketplaceData()
    {
        $marketplace = Mage::helper('M2ePro/Data_Global')->getValue('ebay_marketplace');

        if (!$marketplace instanceof Ess_M2ePro_Model_Marketplace) {
            throw new Ess_M2ePro_Model_Exception_Logic('Marketplace is required for editing Payment Policy.');
        }

        $data = array(
            'id' => $marketplace->getId(),
            'services' => $marketplace->getChildObject()->getPaymentInfo()
        );

        $policyLocalization = $this->getData('policy_localization');

        if (!empty($policyLocalization)) {
            /** @var Ess_M2ePro_Model_Magento_Translate $translator */
            $translator = Mage::getModel('M2ePro/Magento_Translate');
            $translator->setLocale($policyLocalization);
            $translator->init();

            foreach ($data['services'] as $key => $item) {
                $data['services'][$key]['title'] = $translator->__($item['title']);
            }
        }

        return $data;
    }

    //########################################
}