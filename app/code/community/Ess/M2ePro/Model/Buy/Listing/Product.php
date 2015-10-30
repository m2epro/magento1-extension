<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Listing_Product getParentObject()
 */
class Ess_M2ePro_Model_Buy_Listing_Product extends Ess_M2ePro_Model_Component_Child_Buy_Abstract
{
    const SEARCH_SETTINGS_STATUS_NOT_FOUND       = 1;
    const SEARCH_SETTINGS_STATUS_ACTION_REQUIRED = 2;

    //########################################

    /**
     * @var Ess_M2ePro_Model_Buy_Listing_Product_Variation_Manager
     */
    protected $variationManager = NULL;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Buy_Listing_Product');
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        return $this->getParentObject()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Account
     */
    public function getBuyAccount()
    {
        return $this->getAccount()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        return $this->getParentObject()->getMarketplace();
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Marketplace
     */
    public function getBuyMarketplace()
    {
        return $this->getMarketplace()->getChildObject();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        return $this->getParentObject()->getListing();
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Listing
     */
    public function getBuyListing()
    {
        return $this->getListing()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Buy_Listing_Source
     */
    public function getListingSource()
    {
        return $this->getBuyListing()->getSource($this->getActualMagentoProduct());
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->getBuyListing()->getSellingFormatTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Template_SellingFormat
     */
    public function getBuySellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        return $this->getBuyListing()->getSynchronizationTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Template_Synchronization
     */
    public function getBuySynchronizationTemplate()
    {
        return $this->getSynchronizationTemplate()->getChildObject();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Buy_Template_NewProduct
     */
    public function getNewProductTemplate()
    {
        return Mage::helper('M2ePro')->getCachedObject(
            'Buy_Template_NewProduct',$this->getTemplateNewProductId(),NULL,array('template')
        );
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    public function getMagentoProduct()
    {
        return $this->getParentObject()->getMagentoProduct();
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    public function getActualMagentoProduct()
    {
        if (!$this->getVariationManager()->isVariationProduct() ||
            !$this->getVariationManager()->isVariationProductMatched()
        ) {
            return $this->getMagentoProduct();
        }

        if ($this->getMagentoProduct()->isConfigurableType() ||
            $this->getMagentoProduct()->isGroupedType()) {

            $variations = $this->getVariations(true);
            $variation  = reset($variations);
            $options    = $variation->getOptions(true);
            $option     = reset($options);

            return $option->getMagentoProduct();
        }

        return $this->getMagentoProduct();
    }

    //########################################

    public function getBuyItem()
    {
        return Mage::getModel('M2ePro/Buy_Item')->getCollection()
                        ->addFieldToFilter('account_id', $this->getListing()->getAccountId())
                        ->addFieldToFilter('marketplace_id', $this->getListing()->getMarketplaceId())
                        ->addFieldToFilter('sku', $this->getSku())
                        ->setOrder('create_date', Varien_Data_Collection::SORT_ORDER_DESC)
                        ->getFirstItem();
    }

    /**
     * @return Ess_M2ePro_Model_Buy_Listing_Product_Variation_Manager
     */
    public function getVariationManager()
    {
        if (is_null($this->variationManager)) {
            $this->variationManager = Mage::getModel('M2ePro/Buy_Listing_Product_Variation_Manager');
            $this->variationManager->setListingProduct($this->getParentObject());
        }

        return $this->variationManager;
    }

    public function getVariations($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getVariations($asObjects,$filters);
    }

    //########################################

    public function getTemplateNewProductId()
    {
        return $this->getData('template_new_product_id');
    }

    // ---------------------------------------

    public function getSku()
    {
        return $this->getData('sku');
    }

    /**
     * @return int
     */
    public function getGeneralId()
    {
        return (int)$this->getData('general_id');
    }

    // ---------------------------------------

    /**
     * @return float
     */
    public function getOnlinePrice()
    {
        return (float)$this->getData('online_price');
    }

    /**
     * @return int
     */
    public function getOnlineQty()
    {
        return (int)$this->getData('online_qty');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getCondition()
    {
        return (int)$this->getData('condition');
    }

    public function getConditionNote()
    {
        return $this->getData('condition_note');
    }

    // ---------------------------------------

    public function getShippingStandardRate()
    {
        return $this->getData('shipping_standard_rate');
    }

    /**
     * @return int
     */
    public function getShippingExpeditedMode()
    {
        return (int)$this->getData('shipping_expedited_mode');
    }

    public function getShippingExpeditedRate()
    {
        return $this->getData('shipping_expedited_rate');
    }

    //########################################

    public function getSearchSettingsStatus()
    {
        return $this->getData('search_settings_status');
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getSearchSettingsData()
    {
        return $this->getSettings('search_settings_data');
    }

    // ---------------------------------------

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getGeneralIdSearchInfo()
    {
        return $this->getSettings('general_id_search_info');
    }

    //########################################

    /**
     * @return float|int
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getPrice()
    {
        if ($this->getVariationManager()->isVariationProduct() &&
            $this->getVariationManager()->isVariationProductMatched()) {

            $variations = $this->getVariations(true);
            /* @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation = reset($variations);

            return $variation->getChildObject()->getPrice();
        }

        $src = $this->getBuySellingFormatTemplate()->getPriceSource();

        /** @var $calculator Ess_M2ePro_Model_Buy_Listing_Product_PriceCalculator */
        $calculator = Mage::getModel('M2ePro/Buy_Listing_Product_PriceCalculator');
        $calculator->setSource($src)->setProduct($this->getParentObject());
        $calculator->setModifyByCoefficient(true)
                   ->setIsIncreaseByVatPercent(true);

        return $calculator->getProductValue();
    }

    //########################################

    /**
     * @param bool $magentoMode
     * @return int
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getQty($magentoMode = false)
    {
        if ($this->getVariationManager()->isVariationProduct() &&
            $this->getVariationManager()->isVariationProductMatched()) {

            $variations = $this->getVariations(true);
            /* @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation = reset($variations);

            return $variation->getChildObject()->getQty($magentoMode);
        }

        /** @var $calculator Ess_M2ePro_Model_Buy_Listing_Product_QtyCalculator */
        $calculator = Mage::getModel('M2ePro/Buy_Listing_Product_QtyCalculator');
        $calculator->setProduct($this->getParentObject());
        $calculator->setIsMagentoMode($magentoMode);

        return $calculator->getProductValue();
    }

    //########################################

    public function listAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Listing_Product::ACTION_LIST, $params);
    }

    public function relistAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Listing_Product::ACTION_RELIST, $params);
    }

    public function reviseAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Listing_Product::ACTION_REVISE, $params);
    }

    public function stopAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Listing_Product::ACTION_STOP, $params);
    }

    // ---------------------------------------

    protected function processDispatcher($action, array $params = array())
    {
        $dispatcherObject = Mage::getModel('M2ePro/Connector_Buy_Product_Dispatcher');
        return $dispatcherObject->process($action, $this->getId(), $params);
    }

    //########################################

    public function getTrackingAttributes()
    {
        return $this->getListing()->getTrackingAttributes();
    }

    //########################################
}
