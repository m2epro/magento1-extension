<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Amazon_Listing_Product_Variation|Ess_M2ePro_Model_Ebay_Listing_Product_Variation|Ess_M2ePro_Model_Walmart_Listing_Product_Variation getChildObject()
 */
class Ess_M2ePro_Model_Listing_Product_Variation extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    private $listingProductModel = NULL;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Product_Variation');
    }

    //########################################

    protected function _afterSave()
    {
        Mage::helper('M2ePro/Data_Cache_Session')->removeTagValues(
            "listing_product_{$this->getListingProductId()}_variations"
        );
        return parent::_afterSave();
    }

    protected function _beforeDelete()
    {
        Mage::helper('M2ePro/Data_Cache_Session')->removeTagValues(
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

        $options = $this->getOptions(true, array(), true, false);
        foreach ($options as $option) {
            $option->deleteInstance();
        }

        $this->listingProductModel = NULL;

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
        if (is_null($this->listingProductModel)) {
            $this->listingProductModel = Mage::helper('M2ePro/Component')->getComponentObject(
                $this->getComponentMode(),'Listing_Product',$this->getData('listing_product_id')
            );
        }

        return $this->listingProductModel;
    }

    /**
     * @param Ess_M2ePro_Model_Listing_Product $instance
     */
    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $instance)
    {
         $this->listingProductModel = $instance;
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
     * @param array $filters
     * @param bool $tryToGetFromStorage
     * @param bool $throwExceptionIfNoOptions
     * @return Ess_M2ePro_Model_Listing_Product_Variation_Option[]
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getOptions($asObjects = false, array $filters = array(),
                               $tryToGetFromStorage = true, $throwExceptionIfNoOptions = true)
    {
        $storageKey = "listing_product_{$this->getListingProductId()}_variation_{$this->getId()}_options_" .
                      md5((string)$asObjects . Mage::helper('M2ePro')->jsonEncode($filters));

        if ($tryToGetFromStorage && ($cacheData = Mage::helper('M2ePro/Data_Cache_Session')->getValue($storageKey))) {
            return $cacheData;
        }

        /** @var $options Ess_M2ePro_Model_Listing_Product_Variation_Option[] */
        $options = $this->getRelatedComponentItems(
            'Listing_Product_Variation_Option','listing_product_variation_id',$asObjects,$filters
        );

        if ($throwExceptionIfNoOptions && count($options) <= 0) {
            throw new Ess_M2ePro_Model_Exception_Logic('There are no options for a variation product.',
                                                        array(
                                                            'variation_id'       => $this->getId(),
                                                            'listing_product_id' => $this->getListingProductId()
                                                        ));
        }

        if ($asObjects) {
            foreach ($options as $option) {
                $option->setListingProductVariation($this);
            }
        }

        Mage::helper('M2ePro/Data_Cache_Session')->setValue($storageKey, $options, array(
            'listing_product',
            "listing_product_{$this->getListingProductId()}",
            "listing_product_{$this->getListingProductId()}_variation_{$this->getId()}",
            "listing_product_{$this->getListingProductId()}_variation_{$this->getId()}_options"
        ));

        return $options;
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