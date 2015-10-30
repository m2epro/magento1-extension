<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Return_Edit_Form_Data extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayTemplateReturnEditFormData');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/template/return/form/data.phtml');
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

        $template = Mage::helper('M2ePro/Data_Global')->getValue('ebay_template_return');

        if (is_null($template)) {
            return '';
        }

        return $template->getTitle();
    }

    public function getFormData()
    {
        $template = Mage::helper('M2ePro/Data_Global')->getValue('ebay_template_return');

        if (is_null($template) || is_null($template->getId())) {
            return array();
        }

        $data = $template->getData();

        return $data;
    }

    public function getDefault()
    {
        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            return Mage::getSingleton('M2ePro/Ebay_Template_Return')->getDefaultSettingsSimpleMode();
        }

        return Mage::getSingleton('M2ePro/Ebay_Template_Return')->getDefaultSettingsAdvancedMode();
    }

    public function getMarketplaceData()
    {
        $marketplace = Mage::helper('M2ePro/Data_Global')->getValue('ebay_marketplace');

        if (!$marketplace instanceof Ess_M2ePro_Model_Marketplace) {
            throw new Ess_M2ePro_Model_Exception_Logic('Marketplace is required for editing Return Policy.');
        }

        $data = array(
            'id' => $marketplace->getId(),
            'info' => $marketplace->getChildObject()->getReturnPolicyInfo()
        );

        $policyLocalization = $this->getData('policy_localization');

        if (!empty($policyLocalization)) {
            /** @var Ess_M2ePro_Model_Magento_Translate $translator */
            $translator = Mage::getModel('M2ePro/Magento_Translate');
            $translator->setLocale($policyLocalization);
            $translator->init();

            foreach ($data['info']['returns_within'] as $key => $item) {
                $data['info']['returns_within'][$key]['title'] = $translator->__($item['title']);
            }

            foreach ($data['info']['returns_accepted'] as $key => $item) {
                $data['info']['returns_accepted'][$key]['title'] = $translator->__($item['title']);
            }

            foreach ($data['info']['shipping_cost_paid_by'] as $key => $item) {
                $data['info']['shipping_cost_paid_by'][$key]['title'] = $translator->__($item['title']);
            }
        }

        return $data;
    }

    //########################################

    public function canShowHolidayReturnOption()
    {
        $marketplace = Mage::helper('M2ePro/Data_Global')->getValue('ebay_marketplace');

        if (!$marketplace instanceof Ess_M2ePro_Model_Marketplace) {
            throw new Ess_M2ePro_Model_Exception_Logic('Marketplace is required for editing Return Policy.');
        }

        return $marketplace->getChildObject()->isHolidayReturnEnabled();
    }

    //########################################
}