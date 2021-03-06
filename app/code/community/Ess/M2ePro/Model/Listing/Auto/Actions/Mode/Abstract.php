<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Listing_Auto_Actions_Mode_Abstract
{
    /**
     * @var null|Mage_Catalog_Model_Product
     */
    protected $_product = null;

    //########################################

    /**
     * @param Mage_Catalog_Model_Product $product
     */
    public function setProduct(Mage_Catalog_Model_Product $product)
    {
        $this->_product = $product;
    }

    /**
     * @return Mage_Catalog_Model_Product
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getProduct()
    {
        if (!($this->_product instanceof Mage_Catalog_Model_Product)) {
            throw new Ess_M2ePro_Model_Exception_Logic('Property "Product" should be set first.');
        }

        return $this->_product;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing $listing
     * @return Ess_M2ePro_Model_Listing_Auto_Actions_Listing
     */
    protected function getListingObject(Ess_M2ePro_Model_Listing $listing)
    {
        $componentMode = ucfirst($listing->getComponentMode());

        /** @var Ess_M2ePro_Model_Amazon_Listing_Auto_Actions_Listing $object */
        $object = Mage::getModel('M2ePro/'.$componentMode.'_Listing_Auto_Actions_Listing');
        $object->setListing($listing);

        return $object;
    }

    //########################################
}
