<?php

/**
 * @method Ess_M2ePro_Model_Listing_Auto_Category_Group getParentObject()
 */
class Ess_M2ePro_Model_Walmart_Listing_Auto_Category_Group extends Ess_M2ePro_Model_Component_Child_Walmart_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Walmart_Listing_Auto_Category_Group');
    }

    public function getAddingProductTypeId()
    {
        return $this->getData(
            Ess_M2ePro_Model_Resource_Walmart_Listing_Auto_Category_Group::COLUMN_ADDING_PRODUCT_TYPE_ID
        );
    }
}
