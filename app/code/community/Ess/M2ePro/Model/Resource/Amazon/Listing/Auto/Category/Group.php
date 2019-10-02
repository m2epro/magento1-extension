<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Amazon_Listing_Auto_Category_Group
    extends Ess_M2ePro_Model_Resource_Component_Child_Abstract
{
    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Amazon_Listing_Auto_Category_Group', 'listing_auto_category_group_id');
        $this->_isPkAutoIncrement = false;
    }

    //########################################
}
