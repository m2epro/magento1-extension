<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Amazon_Listing_Product_Variation as AmazonListingProductVariation;
use Ess_M2ePro_Model_Ebay_Listing_Product_Variation as EbayListingProductVariation;
use Ess_M2ePro_Model_Walmart_Listing_Product_Variation as WalmartListingProductVariation;

/**
 * @method AmazonListingProductVariation|EbayListingProductVariation|WalmartListingProductVariation getChildObject()
 */
class Ess_M2ePro_Model_Listing_Product_Variation extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    protected $_listingProductModel = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Product_Variation');
    }

    //########################################

    protected function _afterSave()
    {
        Mage::helper('M2ePro/Data_Cache_Runtime')->removeTagValues(
            "listing_product_{$this->getListingProductId()}_variations"
        );
        return parent::_afterSave();
    }

    protected function _beforeDelete()
    {
        Mage::helper('M2ePro/Data_Cache_Runtime')->removeTagValues(
            "listing_product_{$this->getListingProductId()}_variations"
        );
        return parent::_beforeDelete();
    }

    //########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        try {
            foreach ($this->getOptions(true) as $option) {
                $option->deleteInstance();
            }

        // @codingStandardsIgnoreLine
        } catch (\Ess_M2ePro_Model_Exception_Logic $exception) {}

        $this->_listingProductModel = null;

        $this->deleteChildInstance();
        $this->delete();

        return true;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        if ($this->_listingProductModel === null) {
            $this->_listingProductModel = Mage::helper('M2ePro/Component')->getComponentObject(
                $this->getComponentMode(), 'Listing_Product', $this->getData('listing_product_id')
            );
        }

        return $this->_listingProductModel;
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product $instance
     */
    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $instance)
    {
         $this->_listingProductModel = $instance;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        return $this->getListingProduct()->getListing();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        return $this->getListingProduct()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        return $this->getListingProduct()->getMarketplace();
    }

    //########################################

    /**
     * @param bool $asObjects
     * @return Ess_M2ePro_Model_Listing_Product_Variation_Option[]
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getOptions($asObjects = false)
    {
        $storageKey = "listing_product_{$this->getListingProductId()}_variation_{$this->getId()}_options_"
                      .(string)$asObjects;

        if ($cacheData = Mage::helper('M2ePro/Data_Cache_Runtime')->getValue($storageKey)) {
            return $cacheData;
        }

        /** @var Ess_M2ePro_Model_Resource_ActiveRecord_CollectionAbstract $collection */
        $collection = Mage::helper('M2ePro/Component')->getComponentCollection(
            $this->getComponentMode(), 'Listing_Product_Variation_Option'
        );
        $collection->addFieldToFilter('listing_product_variation_id', $this->getId());

        if ($collection->getSize() === 0) {
            throw new Ess_M2ePro_Model_Exception_Logic(
                'There are no options for a variation product.',
                array(
                    'variation_id'       => $this->getId(),
                    'listing_product_id' => $this->getListingProductId()
                )
            );
        }

        foreach ($collection->getItems() as $option) {
            /** @var Ess_M2ePro_Model_Listing_Product_Variation_Option $option */
            $option->setListingProductVariation($this);
        }

        if ($asObjects) {
            $result = $collection->getItems();
        } else {
            $result = $collection->toArray();
            $result = $result['items'];
        }

        Mage::helper('M2ePro/Data_Cache_Runtime')->setValue(
            $storageKey,
            $result,
            array(
                'listing_product',
                "listing_product_{$this->getListingProductId()}",
                "listing_product_{$this->getListingProductId()}_variation_{$this->getId()}",
                "listing_product_{$this->getListingProductId()}_variation_{$this->getId()}_options"
            )
        );

        return $result;
    }

    //########################################

    /**
     * @return int
     */
    public function getListingProductId()
    {
        return (int)$this->getData('listing_product_id');
    }

     //########################################

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getAdditionalData()
    {
        return $this->getSettings('additional_data');
    }

     //########################################
}
