<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Listing_Auto_Category_Group getParentObject()
 */
class Ess_M2ePro_Model_Buy_Listing_Auto_Category_Group extends Ess_M2ePro_Model_Component_Child_Buy_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Buy_Listing_Auto_Category_Group');
    }

    //########################################
}