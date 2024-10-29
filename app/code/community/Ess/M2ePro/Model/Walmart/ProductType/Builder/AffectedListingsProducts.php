<?php

class Ess_M2ePro_Model_Walmart_ProductType_Builder_AffectedListingsProducts
    extends Ess_M2ePro_Model_Template_AffectedListingsProductsAbstract
{
    public function loadCollection(array $filters = array())
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Listing_Product');
        $collection->addFieldToFilter(
            Ess_M2ePro_Model_Resource_Walmart_Listing_Product::COLUMN_PRODUCT_TYPE_ID,
            $this->_model->getId()
        );

        return $collection;
    }
}
