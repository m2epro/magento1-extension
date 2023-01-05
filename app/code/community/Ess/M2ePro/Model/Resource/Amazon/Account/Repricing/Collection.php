<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Amazon_Account_Repricing_Collection
    extends Ess_M2ePro_Model_Resource_Collection_Component_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Account_Repricing');
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Account_Repricing[]
     */
    public function getInvalidAccounts()
    {
        $this->getSelect()->where('invalid = 1');

        return $this->getItems();
    }
}
