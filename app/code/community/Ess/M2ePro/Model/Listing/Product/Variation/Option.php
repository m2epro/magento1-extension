<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Option as AmazonProductVariationOption;
use Ess_M2ePro_Model_Ebay_Listing_Product_Variation_Option as EbayProductVariationOption;
use Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Option as WalmartProductVariationOption;

/**
 * @method AmazonProductVariationOption|EbayProductVariationOption|WalmartProductVariationOption getChildObject()
 */
class Ess_M2ePro_Model_Listing_Product_Variation_Option extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Listing_Product_Variation
     */
    protected $_listingProductVariationModel = null;

    /**
     * @var Ess_M2ePro_Model_Magento_Product_Cache
     */
    protected $_magentoProductModel = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Product_Variation_Option');
    }

    //########################################

    protected function _afterSave()
    {
        $listingProductId = $this->getListingProduct()->getId();
        $variationId      = $this->getListingProductVariationId();

        Mage::helper('M2ePro/Data_Cache_Runtime')->removeTagValues(
            "listing_product_{$listingProductId}_variation_{$variationId}_options"
        );

        return parent::_afterSave();
    }

    protected function _beforeDelete()
    {
        $listingProductId = $this->getListingProduct()->getId();
        $variationId      = $this->getListingProductVariationId();

        Mage::helper('M2ePro/Data_Cache_Runtime')->removeTagValues(
            "listing_product_{$listingProductId}_variation_{$variationId}_options"
        );

        return parent::_beforeDelete();
    }

    //########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->_listingProductVariationModel = null;
        $temp && $this->_magentoProductModel = null;
        return $temp;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product_Variation
     */
    public function getListingProductVariation()
    {
        if ($this->_listingProductVariationModel === null) {
            $this->_listingProductVariationModel = Mage::helper('M2ePro/Component')->getComponentObject(
                $this->getComponentMode(), 'Listing_Product_Variation', $this->getData('listing_product_variation_id')
            );
        }

        return $this->_listingProductVariationModel;
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product_Variation $instance
     */
    public function setListingProductVariation(Ess_M2ePro_Model_Listing_Product_Variation $instance)
    {
         $this->_listingProductVariationModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    public function getMagentoProduct()
    {
        if (!$this->_magentoProductModel) {
            $this->_magentoProductModel = Mage::getModel('M2ePro/Magento_Product_Cache')
                                              ->setStoreId($this->getListing()->getStoreId())
                                              ->setProductId($this->getData('product_id'))
                                              ->setStatisticId($this->getListingProduct()->getId());
        }

        $this->getListingProduct()->getMagentoProduct()->isCacheEnabled()
            ? $this->_magentoProductModel->enableCache() : $this->_magentoProductModel->disableCache();

        return $this->_magentoProductModel;
    }

    /**
     * @param Ess_M2ePro_Model_Magento_Product_Cache $instance
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product_Cache $instance)
    {
        $this->_magentoProductModel = $instance;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        return $this->getListingProductVariation()->getListing();
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        return $this->getListingProductVariation()->getListingProduct();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        return $this->getListingProductVariation()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        return $this->getListingProductVariation()->getMarketplace();
    }

    //########################################

    /**
     * @return int
     */
    public function getListingProductVariationId()
    {
        return (int)$this->getData('listing_product_variation_id');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getProductId()
    {
        return (int)$this->getData('product_id');
    }

    /**
     * @return mixed
     */
    public function getProductType()
    {
        return $this->getData('product_type');
    }

    // ---------------------------------------

    public function getAttribute()
    {
         return $this->getData('attribute');
    }

    public function getOption()
    {
        return $this->getData('option');
    }

    //########################################
}
