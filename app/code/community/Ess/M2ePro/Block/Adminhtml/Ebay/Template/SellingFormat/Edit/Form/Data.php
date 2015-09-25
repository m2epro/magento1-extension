<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_SellingFormat_Edit_Form_Data extends Mage_Adminhtml_Block_Widget
{
    public $attributes = array();

    // ####################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        //------------------------------
        $this->setId('ebayTemplateSellingFormatEditFormData');
        //------------------------------

        $this->setTemplate('M2ePro/ebay/template/sellingFormat/form/data.phtml');

        $this->attributes = Mage::helper('M2ePro/Data_Global')->getValue('ebay_attributes');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        if ($this->isCharity()) {
            $this->getCharityDictionary();
        }
    }

    // ####################################

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

        $template = Mage::helper('M2ePro/Data_Global')->getValue('ebay_template_selling_format');

        if (is_null($template)) {
            return '';
        }

        return $template->getTitle();
    }

    // ####################################

    public function getFormData()
    {
        $template = Mage::helper('M2ePro/Data_Global')->getValue('ebay_template_selling_format');

        if (is_null($template) || is_null($template->getId())) {
            return array();
        }

        $data = $template->getData();

        return $data;
    }

    public function getDefault()
    {
        if (Mage::helper('M2ePro/View_Ebay')->isSimpleMode()) {
            return Mage::getSingleton('M2ePro/Ebay_Template_SellingFormat')->getDefaultSettingsSimpleMode();
        }

        return Mage::getSingleton('M2ePro/Ebay_Template_SellingFormat')->getDefaultSettingsAdvancedMode();
    }

    // ####################################

    public function getCurrency()
    {
        $marketplace = Mage::helper('M2ePro/Data_Global')->getValue('ebay_marketplace');

        if (is_null($marketplace)) {
            return NULL;
        }

        return $marketplace->getChildObject()->getCurrency();
    }

    public function getCurrencyAvailabilityMessage()
    {
        $marketplace = Mage::helper('M2ePro/Data_Global')->getValue('ebay_marketplace');
        $store = Mage::helper('M2ePro/Data_Global')->getValue('ebay_store');
        $template = Mage::helper('M2ePro/Data_Global')->getValue('ebay_template_selling_format');

        if (is_null($template) || is_null($template->getId())) {
            $templateData = $this->getDefault();
            $templateData['component_mode'] = Ess_M2ePro_Helper_Component_Ebay::NICK;
            $usedAttributes = array();
        } else {
            $templateData = $template->getData();
            $usedAttributes = $template->getUsedAttributes();
        }

        $messagesBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_template_messages')
            ->getResultBlock(
                Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT,
                Ess_M2ePro_Helper_Component_Ebay::NICK
            );

        $messagesBlock->setData('template_data', $templateData);
        $messagesBlock->setData('used_attributes', $usedAttributes);
        $messagesBlock->setData('marketplace_id', $marketplace ? $marketplace->getId() : null);
        $messagesBlock->setData('store_id', $store ? $store->getId() : null);

        $messages = $messagesBlock->getMessages();

        if (empty($messages)) {
            return '';
        }

        return $messagesBlock->getMessagesHtml($messages);
    }

    /**
     * @return  Ess_M2ePro_Model_Marketplace|null
    **/
    public function getMarketplace()
    {
        $marketplace = Mage::helper('M2ePro/Data_Global')->getValue('ebay_marketplace');

        if (is_null($marketplace)) {
            return NULL;
        }

        return $marketplace;
    }

    public function getMarketplaceId()
    {
        $marketplace = $this->getMarketplace();

        if (is_null($marketplace)) {
            return NULL;
        }

        return $marketplace->getId();
    }

    private function getCharityDictionary()
    {
        $marketplaceId = $this->getMarketplaceId();

        if (is_null($marketplaceId)) {
            return false;
        }

        /** @var Varien_Db_Adapter_Pdo_Mysql $connRead*/
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableDictMarketplace = Mage::getSingleton('core/resource')->getTableName('m2epro_ebay_dictionary_marketplace');

        $dbSelect = $connRead->select()
            ->from($tableDictMarketplace, 'charities')
            ->where('`marketplace_id` = ?', (int)$marketplaceId);

        $src = json_decode($connRead->fetchOne($dbSelect),true);

        if (!is_null($src)) {
            $charities = array();

            foreach ($src as $charity) {
                $charities[$charity['id']] = array(
                    'id' => $charity['id'],
                    'name' => $charity['name'],
                );
            }

            $this->setData('charities', $charities);

            return true;
        }

        return false;
    }

    // ####################################

    public function isCharity()
    {
        $marketplace = $this->getMarketplace();

        if (!is_null($marketplace) && $marketplace->getChildObject()->isCharityEnabled()) {
           return true;
        }

        return false;
    }

    public function isStpAvailable()
    {
        if (is_null($marketplace = $this->getMarketplace())){
            return true;
        }

        if ($marketplace->getChildObject()->isStpEnabled()) {
            return true;
        }

        return false;
    }

    public function isStpAdvancedAvailable()
    {
        if (is_null($marketplace = $this->getMarketplace())){
            return true;
        }

        if ($marketplace->getChildObject()->isStpAdvancedEnabled()) {
            return true;
        }

        return false;
    }

    public function isMapAvailable()
    {
        if (is_null($marketplace = $this->getMarketplace())){
            return true;
        }

        if ($marketplace->getChildObject()->isMapEnabled()) {
            return true;
        }

        return false;
    }

    // ####################################

    public function isShowMultiCurrencyNotification()
    {
        $marketplace = $this->getMarketplace();

        if (is_null($marketplace)) {
           return false;
        }

        if (!$marketplace->getChildObject()->isMultiCurrencyEnabled()) {
            return false;
        }

        $marketplaceId = $marketplace->getId();

        $configValue = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            "/view/ebay/multi_currency_marketplace_{$marketplaceId}/", 'notification_shown'
        );

        if ($configValue) {
            return false;
        }

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            "/view/ebay/multi_currency_marketplace_{$marketplaceId}/", 'notification_shown', 1
        );

        return true;
    }

    // ####################################

    public function getTaxCategoriesInfo()
    {
        $marketplacesCollection = Mage::helper('M2ePro/Component_Ebay')->getModel('Marketplace')
            ->getCollection()
            ->addFieldToFilter('status',Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
            ->setOrder('sorder','ASC');

        $marketplacesCollection->getSelect()->limit(1);

        $marketplaces = $marketplacesCollection->getItems();

        if (count($marketplaces) == 0) {
            return array();
        }

        return array_shift($marketplaces)->getChildObject()->getTaxCategoryInfo();
    }

    // ####################################
}