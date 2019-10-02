<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Listing_Product_Instruction extends Ess_M2ePro_Model_Abstract
{
    /** @var Ess_M2ePro_Model_Listing_Product */
    protected $_listingProduct = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Product_Instruction');
    }

    //########################################

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->_listingProduct = $listingProduct;
    }

    public function getListingProduct()
    {
        if ($this->getId() === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Model must be loaded.');
        }

        if ($this->_listingProduct !== null) {
            return $this->_listingProduct;
        }

        $this->_listingProduct = Mage::helper('M2ePro/Component')->getComponentObject(
            $this->getComponent(), 'Listing_Product', $this->getListingProductId()
        );

        return $this->_listingProduct;
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

    public function getType()
    {
        return $this->getData('type');
    }

    public function getInitiator()
    {
        return $this->getData('initiator');
    }

    public function getPriority()
    {
        return (int)$this->getData('priority');
    }

    public function getAdditionalData()
    {
        return $this->getSettings('additional_data');
    }

    public function getSkipUntil()
    {
        return $this->getData('skip_until');
    }

    //########################################
}
