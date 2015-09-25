<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Order_Item[] getItems()
 */
class Ess_M2ePro_Model_Mysql4_Order_Item_Collection
    extends Ess_M2ePro_Model_Mysql4_Collection_Component_Parent_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Order_Item');
    }

    // ########################################
}