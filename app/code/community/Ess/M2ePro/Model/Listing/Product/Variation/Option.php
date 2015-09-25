<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Listing_Product_Variation_Option extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Listing_Product_Variation
     */
    private $listingProductVariationModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Magento_Product_Cache
     */
    protected $magentoProductModel = NULL;

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Product_Variation_Option');
    }

    // ########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->listingProductVariationModel = NULL;
        $temp && $this->magentoProductModel = NULL;
        return $temp;
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product_Variation
     */
    public function getListingProductVariation()
    {
        if (is_null($this->listingProductVariationModel)) {
            $this->listingProductVariationModel = Mage::helper('M2ePro/Component')->getComponentObject(
                $this->getComponentMode(),'Listing_Product_Variation',$this->getData('listing_product_variation_id')
            );
        }

        return $this->listingProductVariationModel;
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product_Variation $instance
     */
    public function setListingProductVariation(Ess_M2ePro_Model_Listing_Product_Variation $instance)
    {
         $this->listingProductVariationModel = $instance;
    }

    //-----------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    public function getMagentoProduct()
    {
        if (!$this->magentoProductModel) {
            $this->magentoProductModel = Mage::getModel('M2ePro/Magento_Product_Cache')
                                                    ->setStoreId($this->getListing()->getStoreId())
                                                    ->setProductId($this->getData('product_id'))
                                                    ->setStatisticId($this->getListingProduct()->getId());
        }

        $this->getListingProduct()->getMagentoProduct()->isCacheEnabled()
            ? $this->magentoProductModel->enableCache() : $this->magentoProductModel->disableCache();

        return $this->magentoProductModel;
    }

    /**
     * @param Ess_M2ePro_Model_Magento_Product_Cache $instance
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product_Cache $instance)
    {
        $this->magentoProductModel = $instance;
    }

    // ########################################

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

    //-----------------------------------------

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

    // ########################################

    public function getListingProductVariationId()
    {
        return (int)$this->getData('listing_product_variation_id');
    }

    //----------------------------------------

    public function getProductId()
    {
        return (int)$this->getData('product_id');
    }

    public function getProductType()
    {
        return $this->getData('product_type');
    }

    //----------------------------------------

    public function getAttribute()
    {
         return $this->getData('attribute');
    }

    public function getOption()
    {
        return $this->getData('option');
    }

    // ########################################
}