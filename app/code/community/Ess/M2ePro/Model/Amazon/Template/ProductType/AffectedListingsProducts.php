<?php

class Ess_M2ePro_Model_Amazon_Template_ProductType_AffectedListingsProducts
    extends Ess_M2ePro_Model_Template_AffectedListingsProductsAbstract
{
    /**
     * @return Ess_M2ePro_Model_Resource_Amazon_Listing_Product_Collection
     */
    public function loadCollection(array $filters = array())
    {
        /** @var Ess_M2ePro_Model_Resource_Amazon_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->addFieldToFilter('template_product_type_id', $this->_model->getId());

        return $listingProductCollection;
    }
}