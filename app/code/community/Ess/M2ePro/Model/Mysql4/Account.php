<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Mysql4_Account
    extends Ess_M2ePro_Model_Mysql4_Component_Parent_Abstract
{
    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Account', 'id');
    }

    //########################################
}