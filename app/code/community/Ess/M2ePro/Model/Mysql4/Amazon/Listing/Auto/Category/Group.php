<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Amazon_Listing_Auto_Category_Group
    extends Ess_M2ePro_Model_Mysql4_Component_Child_Abstract
{
    // ########################################

    public function _construct()
    {
        $this->_init('M2ePro/Amazon_Listing_Auto_Category_Group', 'listing_auto_category_group_id');
        $this->_isPkAutoIncrement = false;
    }

    // ########################################
}