<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Mysql4_LockedObject
    extends Ess_M2ePro_Model_Mysql4_Abstract
{
    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/LockedObject', 'id');
    }

    //########################################
}