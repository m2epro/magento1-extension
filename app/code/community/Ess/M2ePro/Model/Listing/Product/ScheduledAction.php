<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Listing_Product_ScheduledAction extends Ess_M2ePro_Model_Abstract
{
    /** @var Ess_M2ePro_Model_Listing_Product */
    private $listingProduct = NULL;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Product_ScheduledAction');
    }

    //########################################

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->listingProduct = $listingProduct;
    }

    public function getListingProduct()
    {
        if (is_null($this->getId())) {
            throw new Ess_M2ePro_Model_Exception_Logic('Model must be loaded.');
        }

        if (!is_null($this->listingProduct)) {
            return $this->listingProduct;
        }

        $this->listingProduct = Mage::helper('M2ePro/Component')->getComponentObject(
            $this->getComponent(), 'Listing_Product', $this->getListingProductId()
        );

        return $this->listingProduct;
    }

    //########################################

    public function getListingProductId()
    {
        return (int)$this->getData('listing_product_id');
    }

    public function getComponent()
    {
        return $this->getData('component');
    }

    public function getActionType()
    {
        return (int)$this->getData('action_type');
    }

    public function isActionTypeList()
    {
        return $this->getActionType() == Ess_M2ePro_Model_Listing_Product::ACTION_LIST;
    }

    public function isActionTypeRelist()
    {
        return $this->getActionType() == Ess_M2ePro_Model_Listing_Product::ACTION_RELIST;
    }

    public function isActionTypeRevise()
    {
        return $this->getActionType() == Ess_M2ePro_Model_Listing_Product::ACTION_REVISE;
    }

    public function isActionTypeStop()
    {
        return $this->getActionType() == Ess_M2ePro_Model_Listing_Product::ACTION_STOP;
    }

    public function isActionTypeDelete()
    {
        return $this->getActionType() == Ess_M2ePro_Model_Listing_Product::ACTION_DELETE;
    }

    public function isForce()
    {
        return (bool)$this->getData('is_force');
    }

    public function getTag()
    {
        return $this->getData('tag');
    }

    public function getAdditionalData()
    {
        return $this->getSettings('additional_data');
    }

    //########################################
}