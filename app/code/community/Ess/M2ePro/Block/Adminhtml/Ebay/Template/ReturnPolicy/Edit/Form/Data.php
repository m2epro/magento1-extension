<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_ReturnPolicy_Edit_Form_Data extends Mage_Adminhtml_Block_Widget
{
    const RETURNS_WITHIN_DEFAULT_VALUE = 'Days_30';
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayTemplateReturnEditFormData');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/template/returnPolicy/form/data.phtml');
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

        $template = Mage::helper('M2ePro/Data_Global')->getValue('ebay_template_return_policy');

        if ($template === null) {
            return '';
        }

        return $template->getTitle();
    }

    public function getFormData()
    {
        $template = Mage::helper('M2ePro/Data_Global')->getValue('ebay_template_return_policy');

        if ($template === null || $template->getId() === null) {
            return array();
        }

        $data = $template->getData();

        return $data;
    }

    public function getDefault()
    {
        return Mage::getModel('M2ePro/Ebay_Template_ReturnPolicy_Builder')->getDefaultData();
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
        $translator = Mage::helper('M2ePro');

        if (!empty($policyLocalization)) {
            /** @var Ess_M2ePro_Model_Magento_Translate $translator */
            $translator = Mage::getModel('M2ePro/Magento_Translate');
            $translator->setLocale($policyLocalization);
            $translator->init();
        }

        foreach ($this->getDictionaryInfo('returns_within', $marketplace) as $key => $item) {
            $data['info']['returns_within'][$key]['title'] = $translator->__($item['title']);
        }

        foreach ($this->getDictionaryInfo('returns_accepted', $marketplace) as $key => $item) {
            $data['info']['returns_accepted'][$key]['title'] = $translator->__($item['title']);
        }

        foreach ($this->getDictionaryInfo('refund', $marketplace) as $key => $item) {
            $data['info']['refund'][$key]['title'] = $translator->__($item['title']);
        }

        foreach ($this->getDictionaryInfo('shipping_cost_paid_by', $marketplace) as $key => $item) {
            $data['info']['shipping_cost_paid_by'][$key]['title'] = $translator->__($item['title']);
        }

        // ---------------------------------------

        foreach ($this->getInternationalDictionaryInfo('returns_within', $marketplace) as $key => $item) {
            $data['info']['international_returns_within'][$key]['ebay_id'] = $item['ebay_id'];
            $data['info']['international_returns_within'][$key]['title'] = $translator->__($item['title']);
        }

        foreach ($this->getInternationalDictionaryInfo('returns_accepted', $marketplace) as $key => $item) {
            $data['info']['international_returns_accepted'][$key]['ebay_id'] = $item['ebay_id'];
            $data['info']['international_returns_accepted'][$key]['title'] = $translator->__($item['title']);
        }

        foreach ($this->getInternationalDictionaryInfo('refund', $marketplace) as $key => $item) {
            $data['info']['international_refund'][$key]['ebay_id'] = $item['ebay_id'];
            $data['info']['international_refund'][$key]['title'] = $translator->__($item['title']);
        }

        foreach ($this->getInternationalDictionaryInfo('shipping_cost_paid_by', $marketplace)as $key => $item) {
            $data['info']['international_shipping_cost_paid_by'][$key]['ebay_id'] = $item['ebay_id'];
            $data['info']['international_shipping_cost_paid_by'][$key]['title'] = $translator->__($item['title']);
        }

        return $data;
    }

    //########################################

    public function canShowGeneralBlock()
    {
        $marketplace = Mage::helper('M2ePro/Data_Global')->getValue('ebay_marketplace');

        if (!$marketplace instanceof Ess_M2ePro_Model_Marketplace) {
            throw new Ess_M2ePro_Model_Exception_Logic('Marketplace is required for editing Return Policy.');
        }

        return $marketplace->getChildObject()->isReturnDescriptionEnabled();
    }

    //########################################

    protected function getDictionaryInfo($key, Ess_M2ePro_Model_Marketplace $marketplace)
    {
        $returnPolicyInfo = $marketplace->getChildObject()->getReturnPolicyInfo();
        return !empty($returnPolicyInfo[$key]) ? $returnPolicyInfo[$key] : array();
    }

    protected function getInternationalDictionaryInfo($key, Ess_M2ePro_Model_Marketplace $marketplace)
    {
        $returnPolicyInfo = $marketplace->getChildObject()->getReturnPolicyInfo();

        if (!empty($returnPolicyInfo['international_'.$key])) {
            return $returnPolicyInfo['international_'.$key];
        }

        return $this->getDictionaryInfo($key, $marketplace);
    }

    /**
     * @param array $options
     *
     * @return mixed|string
     */
    public function getDefaultDaysValueForReturnPolicy(array $options)
    {
        $result = '';
        foreach ($options as $option) {
            if ($option['ebay_id'] === self::RETURNS_WITHIN_DEFAULT_VALUE) {
                $result = $option['ebay_id'];
            }
        }
        if (!$result) {
            $result = array_shift($options);
            $result = is_array($result) ? $result['ebay_id'] : '';
        }

        return $result;
    }
}
