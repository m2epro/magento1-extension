<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Mysql4_Ebay_Account_PickupStore_Log
    extends Ess_M2ePro_Model_Mysql4_Log_Abstract
{
    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Ebay_Account_PickupStore_Log', 'id');
    }

    public function getLastActionIdConfigKey()
    {
        return 'ebay_pickup_store';
    }

    //########################################
}