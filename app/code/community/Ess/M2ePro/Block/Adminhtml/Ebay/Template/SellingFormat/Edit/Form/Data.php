<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_SellingFormat_Edit_Form_Data extends Mage_Adminhtml_Block_Widget
{
    public $attributes = array();

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayTemplateSellingFormatEditFormData');
        // ---------------------------------------

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

        $data = $template->getData();

        return $data;
    }

    public function getDefault()
    {
        return Mage::getSingleton('M2ePro/Ebay_Template_SellingFormat')->getDefaultSettings();
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

        if ($marketplace === null) {
            return null;
        }

        return $marketplace;
    }

    public function getMarketplaceId()
    {
        $marketplace = $this->getMarketplace();

        if ($marketplace === null) {
            return null;
        }

        return $marketplace->getId();
    }

    protected function getCharityDictionary()
    {
        $marketplaceId = $this->getMarketplaceId();

        if ($marketplaceId === null) {
            return false;
        }

        /** @var Varien_Db_Adapter_Pdo_Mysql $connRead*/
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableDictMarketplace = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_dictionary_marketplace');

        $dbSelect = $connRead->select()
            ->from($tableDictMarketplace, 'charities')
            ->where('`marketplace_id` = ?', (int)$marketplaceId);

        $src = Mage::helper('M2ePro')->jsonDecode($connRead->fetchOne($dbSelect));

        if ($src !== null) {
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

    //########################################

    public function isCharity()
    {
        $marketplace = $this->getMarketplace();

        if ($marketplace !== null && $marketplace->getChildObject()->isCharityEnabled()) {
           return true;
        }

        return false;
    }

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
