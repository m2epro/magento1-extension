<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Mysql4_Buy_Listing_Auto_Category_Group
    extends Ess_M2ePro_Model_Mysql4_Component_Child_Abstract
{
    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Buy_Listing_Auto_Category_Group', 'listing_auto_category_group_id');
        $this->_isPkAutoIncrement = false;
    }

    //########################################
}