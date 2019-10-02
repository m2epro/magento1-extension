<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    protected $_listingProduct = null;

    /**
     * @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager
     */
    protected $_variationManager = null;

    protected $_isCacheEnabled = false;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager $variationManager
     */
    public function setVariationManager(Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager $variationManager)
    {
        $this->_variationManager = $variationManager;
        $this->_listingProduct   = $variationManager->getListingProduct();
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager
     */
    public function getVariationManager()
    {
        return $this->_variationManager;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        return $this->_listingProduct;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Product
     */
    public function getWalmartListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        return $this->getListingProduct()->getListing();
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing
     */
    public function getWalmartListing()
    {
        return $this->getListing()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    public function getMagentoProduct()
    {
        return $this->getListingProduct()->getMagentoProduct();
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    public function getActualMagentoProduct()
    {
        return $this->getWalmartListingProduct()->getActualMagentoProduct();
    }

    //########################################

    /**
     * @return bool
     */
    public function isCacheEnabled()
    {
        return $this->_isCacheEnabled;
    }

    /**
     * @return $this
     */
    public function enableCache()
    {
        $this->_isCacheEnabled = true;

        $this->getMagentoProduct()->enableCache();
        $this->getActualMagentoProduct()->enableCache();

        return $this;
    }

    /**
     * @return $this
     */
    public function disableCache()
    {
        $this->_isCacheEnabled = false;

        $this->getMagentoProduct()->disableCache();
        $this->getActualMagentoProduct()->disableCache();

        return $this;
    }

    //########################################

    abstract public function clearTypeData();

    //########################################
}
