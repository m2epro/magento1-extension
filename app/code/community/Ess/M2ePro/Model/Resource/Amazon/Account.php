<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Amazon_Account
    extends Ess_M2ePro_Model_Resource_Component_Child_Abstract
{
    const COLUMN_ACCOUNT_ID = 'account_id';
    const COLUMN_MARKETPLACE_ID = 'marketplace_id';
    const COLUMN_MERCHANT_ID = 'merchant_id';

    protected $_isPkAutoIncrement = false;

    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Amazon_Account', 'account_id');
        $this->_isPkAutoIncrement = false;
    }

    //########################################
}
