<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Transferring_Step_Destination extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingTransferringStepDestination');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/listing/transferring/step/destination.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $data = array(
            'id'      => 'continue_button_destination',
            'class'   => 'confirm_button',
            'label'   => Mage::helper('M2ePro')->__('Continue'),
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('continue_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = array(
            'id'      => 'confirm_button_destination',
            'class'   => 'confirm_button',
            'label'   => Mage::helper('M2ePro')->__('Confirm'),
            'onclick' => "EbayListingTransferringHandlerObj.validate() &&
                          EbayListingTransferringHandlerObj.synchronizeMarketplace(
                              'EbayListingTransferringHandlerObj.confirm();');",
            'style'   => 'display: none;'
        );
        $buttonBlock = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('confirm_button', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $listing = $this->_getEbayListing();

        $sourceMarketplace = $listing->getMarketplace();

        $this->setData('source_listing', $listing);
        $this->setData('source_account', $listing->getAccount());
        $this->setData('source_marketplace', $sourceMarketplace);
        $this->setData('source_store', $this->_getStore());
        // ---------------------------------------

        // ---------------------------------------
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account')->setOrder('title', 'ASC');
        $accounts = array();
        foreach ($collection->getItems() as $account) {
            $accounts[] = array(
                'id'    => $account->getId(),
                'title' => Mage::helper('M2ePro')->escapeHtml($account->getTitle()),
            );
        }

        $this->setData('accounts', $accounts);
        // ---------------------------------------

        // ---------------------------------------
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Marketplace')
            ->setOrder('sorder', 'ASC')
            ->setOrder('title', 'ASC');

        $marketplaces = array();
        foreach ($collection->getItems() as $id => $marketplace) {
            $marketplaces[$id] = array(
                'id'     => $marketplace->getId(),
                'title'  => Mage::helper('M2ePro')->escapeHtml($marketplace->getTitle()),
                'url'    => $marketplace->getUrl(),
                'status' => $marketplace->getStatus() == Ess_M2ePro_Model_Marketplace::STATUS_ENABLE ? '1' : '0',
            );
        }

        $this->setData('marketplaces', $marketplaces);
        // ---------------------------------------

        // ---------------------------------------
        $storeSwitcherBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_storeSwitcher')
            ->setData('id', 'transferring_store_id');

        $this->setChild('store_switcher', $storeSwitcherBlock);
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################

    public function isAllowedStep()
    {
        return (bool)$this->getData('is_allowed');
    }

    //########################################

    public function isCustomSettings()
    {
        $productsIds = $this->getData('products_ids');
        $productsIds = explode(',', $productsIds);
        $productsIds = array_filter($productsIds);

        if (empty($productsIds)) {
            return false;
        }

        // ---------------------------------------
        $paymentTemplateColumnName = Mage::getModel('M2ePro/Ebay_Template_Manager')
            ->setTemplate(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_PAYMENT)->getModeColumnName();
        $shippingTemplateColumnName = Mage::getModel('M2ePro/Ebay_Template_Manager')
            ->setTemplate(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SHIPPING)->getModeColumnName();
        $returnTemplateColumnName = Mage::getModel('M2ePro/Ebay_Template_Manager')
            ->setTemplate(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_RETURN_POLICY)->getModeColumnName();
        // ---------------------------------------

        $listingProducts = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product')
            ->addFieldToFilter('id', array('in' => $productsIds))
            ->addFieldToFilter($paymentTemplateColumnName, Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT)
            ->addFieldToFilter($shippingTemplateColumnName, Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT)
            ->addFieldToFilter($returnTemplateColumnName, Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT);

        return (int)$listingProducts->getSize() != count($productsIds);
    }

    //########################################

    public function isAutoCategories()
    {
        $ebayListing = $this->_getEbayListing();

        if ($ebayListing->isAutoGlobalAddingModeAddAndAssignCategory() ||
            $ebayListing->isAutoWebsiteAddingModeAddAndAssignCategory()) {
            return true;
        }

        $categories = $ebayListing->getAutoCategories(true);

        if (!empty($categories)) {
            foreach ($categories as $category) {
                if ($category->isAddingModeAddAndAssignCategory()) {
                    return true;
                }
            }
        }

        return false;
    }

    //########################################

    protected function _getStore()
    {
        $listing = $this->_getEbayListing();

        // Get store filter
        // ---------------------------------------
        $storeId = $listing['store_id'];
        // ---------------------------------------

        return Mage::app()->getStore((int)$storeId);
    }

    //########################################

    protected function _getEbayListing()
    {
        if (!$listingId = $this->getData('listing_id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        return Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', (int)$listingId)->getChildObject();
    }

    //########################################
}
