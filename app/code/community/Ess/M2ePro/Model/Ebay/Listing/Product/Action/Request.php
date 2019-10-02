<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request
{
    /**
     * @var array
     */
    protected $_cachedData = array();

    /**
     * @var array
     */
    protected $_params = array();

    /**
     * @var Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator
     */
    protected $_configurator = null;

    /**
     * @var array
     */
    protected $_warningMessages = array();

    /**
     * @var array
     */
    protected $_metaData = array();

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    protected $_listingProduct = null;

    /**
     * @var bool
     */
    protected $_isVariationItem = false;

    //########################################

    public function setCachedData(array $data)
    {
        $this->_cachedData = $data;
    }

    /**
     * @return array
     */
    public function getCachedData()
    {
        return $this->_cachedData;
    }

    //########################################

    public function setParams(array $params = array())
    {
        $this->_params = $params;
    }

    /**
     * @return array
     */
    protected function getParams()
    {
        return $this->_params;
    }

    // ---------------------------------------

    public function setConfigurator(Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator $object)
    {
        $this->_configurator = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product_Action_Configurator
     */
    protected function getConfigurator()
    {
        return $this->_configurator;
    }

    //########################################

    protected function addWarningMessage($message)
    {
        $this->_warningMessages[sha1($message)] = $message;
    }

    /**
     * @return array
     */
    public function getWarningMessages()
    {
        return $this->_warningMessages;
    }

    //########################################

    protected function addMetaData($key, $value)
    {
        $this->_metaData[$key] = $value;
    }

    public function getMetaData()
    {
        return $this->_metaData;
    }

    public function setMetaData($value)
    {
        $this->_metaData = $value;
        return $this;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $object
     */
    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $object)
    {
        $this->_listingProduct = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    protected function getListingProduct()
    {
        return $this->_listingProduct;
    }

    //########################################

    public function setIsVariationItem($value)
    {
        $this->_isVariationItem = (bool)$value;
    }

    protected function getIsVariationItem()
    {
        return $this->_isVariationItem;
    }

    //########################################

    abstract public function getData();

    //########################################

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getListing()->getMarketplace();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Marketplace
     */
    protected function getEbayMarketplace()
    {
        return $this->getMarketplace()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Account
     */
    protected function getAccount()
    {
        return $this->getListing()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Account
     */
    protected function getEbayAccount()
    {
        return $this->getAccount()->getChildObject();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product
     */
    protected function getEbayListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    protected function getMagentoProduct()
    {
        return $this->getListingProduct()->getMagentoProduct();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    protected function getListing()
    {
        return $this->getListingProduct()->getListing();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing
     */
    protected function getEbayListing()
    {
        return $this->getListing()->getChildObject();
    }

    //########################################
}
