<?php

class Ess_M2ePro_Model_Resource_Walmart_Listing
    extends Ess_M2ePro_Model_Resource_Component_Child_Abstract
{
    const COLUMN_AUTO_GLOBAL_ADDING_PRODUCT_TYPE_ID = 'auto_global_adding_product_type_id';
    const COLUMN_AUTO_WEBSITE_ADDING_PRODUCT_TYPE_ID = 'auto_website_adding_product_type_id';

    protected $_isPkAutoIncrement = false;

    public function _construct()
    {
        $this->_init('M2ePro/Walmart_Listing', 'listing_id');
    }
}
