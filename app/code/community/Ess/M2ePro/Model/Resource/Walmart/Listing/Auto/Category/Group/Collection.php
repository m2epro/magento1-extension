<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Walmart_Listing_Auto_Category_Group_Collection
    extends Ess_M2ePro_Model_Resource_Collection_Component_Child_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Walmart_Listing_Auto_Category_Group');
    }

    //########################################
}
