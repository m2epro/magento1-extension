<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    protected $_listingProduct = null;

    /**
     * @var array
     */
    protected $_cachedData = array();

    /**
     * @var array
     */
    protected $_params = array();

    /**
     * @var array
     */
    protected $_warningMessages = array();

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return $this
     */
    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->_listingProduct = $listingProduct;
        return $this;
    }

    // ---------------------------------------

    /**
     * @param array $data
     * @return $this
     */
    public function setCachedData(array $data)
    {
        $this->_cachedData = $data;
        return $this;
    }

    // ---------------------------------------

    /**
     * @param array $params
     * @return $this
     */
    public function setParams(array $params = array())
    {
        $this->_params = $params;
        return $this;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    protected function getMarketplace()
    {
        return $this->getAmazonAccount()->getMarketplace();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Marketplace
     */
    protected function getAmazonMarketplace()
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
     * @return Ess_M2ePro_Model_Amazon_Account
     */
    protected function getAmazonAccount()
    {
        return $this->getAccount()->getChildObject();
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
     * @return Ess_M2ePro_Model_Amazon_Listing
     */
    protected function getAmazonListing()
    {
        return $this->getListing()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    protected function getListingProduct()
    {
        return $this->_listingProduct;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product
     */
    protected function getAmazonListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    protected function getMagentoProduct()
    {
        return $this->getListingProduct()->getMagentoProduct();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager
     */
    protected function getVariationManager()
    {
        return $this->getAmazonListingProduct()->getVariationManager();
    }

    //########################################

    protected function searchNotFoundAttributes()
    {
        $this->getMagentoProduct()->clearNotFoundAttributes();
    }

    protected function processNotFoundAttributes($title)
    {
        $attributes = $this->getMagentoProduct()->getNotFoundAttributes();

        if (empty($attributes)) {
            return true;
        }

        $this->addNotFoundAttributesMessages($title, $attributes);

        return false;
    }

    // ---------------------------------------

    protected function addNotFoundAttributesMessages($title, array $attributes)
    {
        $attributesTitles = array();

        foreach ($attributes as $attribute) {
            $attributesTitles[] = Mage::helper('M2ePro/Magento_Attribute')
                ->getAttributeLabel(
                    $attribute,
                    $this->getListing()->getStoreId()
                );
        }

        // M2ePro_TRANSLATIONS
        // %attribute_title%: Attribute(s) %attributes% were not found in this Product and its value was not sent.
        $this->addWarningMessage(
            Mage::helper('M2ePro')->__(
                '%attribute_title%: Attribute(s) %attributes% were not found'.
                ' in this Product and its value was not sent.',
                Mage::helper('M2ePro')->__($title), implode(',', $attributesTitles)
            )
        );
    }

    //########################################

    protected function addWarningMessage($message)
    {
        $this->_warningMessages[sha1($message)] = $message;
        return $this;
    }

    /**
     * @return array
     */
    public function getWarningMessages()
    {
        return $this->_warningMessages;
    }

    //########################################

    /**
     * @return array
     */
    abstract public function getData();

    //########################################
}
