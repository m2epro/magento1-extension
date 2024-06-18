<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_SellingFormat_Edit_Form_Data extends Mage_Adminhtml_Block_Widget
{
    public $attributes = array();
    public $enabledMarketplaces = null;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayTemplateSellingFormatEditFormData');

        $this->setTemplate('M2ePro/ebay/template/sellingFormat/form/data.phtml');

        $this->attributes = Mage::helper('M2ePro/Data_Global')->getValue('ebay_attributes');
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

        $template = Mage::helper('M2ePro/Data_Global')->getValue('ebay_template_selling_format');

        if ($template === null) {
            return '';
        }

        return $template->getTitle();
    }

    //########################################

    public function getFormData()
    {
        $template = Mage::helper('M2ePro/Data_Global')->getValue('ebay_template_selling_format');

        if ($template === null || $template->getId() === null) {
            return array();
        }

        return $template->getData();
    }

    public function getDefault()
    {
        return Mage::getModel('M2ePro/Ebay_Template_SellingFormat_Builder')->getDefaultData();
    }

    //########################################

    public function getCurrency()
    {
        $marketplace = Mage::helper('M2ePro/Data_Global')->getValue('ebay_marketplace');

        if ($marketplace === null) {
            return null;
        }

        return $marketplace->getChildObject()->getCurrency();
    }

    public function getCurrencyAvailabilityMessage()
    {
        $marketplace = Mage::helper('M2ePro/Data_Global')->getValue('ebay_marketplace');
        $store = Mage::helper('M2ePro/Data_Global')->getValue('ebay_store');
        $template = Mage::helper('M2ePro/Data_Global')->getValue('ebay_template_selling_format');

        if ($template === null || $template->getId() === null) {
            $templateData = $this->getDefault();
            $templateData['component_mode'] = Ess_M2ePro_Helper_Component_Ebay::NICK;
        } else {
            $templateData = $template->getData();
        }

        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Template_SellingFormat_Messages $messagesBlock */
        $messagesBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_template_sellingFormat_messages');
        $messagesBlock->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);
        $messagesBlock->setTemplateNick(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT);

        $messagesBlock->setData('template_data', $templateData);
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
        return Mage::helper('M2ePro/Data_Global')->getValue('ebay_marketplace');
    }

    public function getMarketplaceId()
    {
        $marketplace = $this->getMarketplace();

        if ($marketplace === null) {
            return null;
        }

        return $marketplace->getId();
    }

    public function getEnabledMarketplaces()
    {
        if ($this->enabledMarketplaces === null) {
            if ($this->getMarketplace() !== null) {
                $this->enabledMarketplaces = array($this->getMarketplace());
            } else {
                $collection = Mage::getModel('M2ePro/Marketplace')->getCollection();
                $collection->addFieldToFilter('component_mode', Ess_M2ePro_Helper_Component_Ebay::NICK);
                $collection->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE);
                $collection->setOrder('sorder', 'ASC');

                $this->enabledMarketplaces = $collection->getItems();
            }
        }

        return $this->enabledMarketplaces;
    }

    //########################################

    public function isStpAvailable()
    {
        $marketplace = $this->getMarketplace();
        if ($marketplace === null) {
            return true;
        }

        if ($marketplace->getChildObject()->isStpEnabled()) {
            return true;
        }

        return false;
    }

    public function isStpAdvancedAvailable()
    {
        $marketplace = $this->getMarketplace();
        if ($marketplace === null) {
            return true;
        }

        if ($marketplace->getChildObject()->isStpAdvancedEnabled()) {
            return true;
        }

        return false;
    }

    public function isMapAvailable()
    {
        $marketplace = $this->getMarketplace();
        if ($marketplace === null) {
            return true;
        }

        if ($marketplace->getChildObject()->isMapEnabled()) {
            return true;
        }

        return false;
    }

    //########################################

    public function getTaxCategoriesInfo()
    {
        $marketplacesCollection = Mage::helper('M2ePro/Component_Ebay')->getModel('Marketplace')
            ->getCollection()
            ->addFieldToFilter('status', Ess_M2ePro_Model_Marketplace::STATUS_ENABLE)
            ->setOrder('sorder', 'ASC');

        $marketplacesCollection->getSelect()->limit(1);

        $marketplaces = $marketplacesCollection->getItems();

        if (empty($marketplaces)) {
            return array();
        }

        return array_shift($marketplaces)->getChildObject()->getTaxCategoryInfo();
    }

    //########################################
}
