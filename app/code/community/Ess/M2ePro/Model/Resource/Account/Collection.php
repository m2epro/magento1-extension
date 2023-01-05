<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Account_Collection
    extends Ess_M2ePro_Model_Resource_Collection_Component_Parent_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Account');
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Resource_Amazon_Account[]
     */
    public function getAccountsWithValidRepricingAccount()
    {
        $amazonRepricingAccountResource =
            Mage::getResourceModel('M2ePro/Amazon_Account_Repricing');

        $this->getSelect()->joinInner(
            array('aar' => $amazonRepricingAccountResource->getMainTable()),
            'aar.account_id = main_table.id',
            array()
        );

        $this->getSelect()->where('invalid = 0');

        return $this->getItems();
    }
}
