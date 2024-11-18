<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Amazon_Listing
    extends Ess_M2ePro_Model_Resource_Component_Child_Abstract
{
    const COLUMN_AUTO_GLOBAL_ADDING_PRODUCT_TYPE_TEMPLATE_ID = 'auto_global_adding_product_type_template_id';
    const COLUMN_AUTO_WEBSITE_ADDING_PRODUCT_TYPE_TEMPLATE_ID = 'auto_website_adding_product_type_template_id';

    protected $_isPkAutoIncrement = false;

    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Amazon_Listing', 'listing_id');
    }

    //########################################
}
