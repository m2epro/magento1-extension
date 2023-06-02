<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Ebay_Template_Shipping_Collection
    extends Ess_M2ePro_Model_Resource_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Shipping');
    }

    public function applyLinkedAccountFilter($accountId)
    {
        $this
            ->getSelect()
            ->where('local_shipping_rate_table LIKE ?', "%\"$accountId\":%")
            ->orWhere('international_shipping_rate_table LIKE ?', "%\"$accountId\":%");

        return $this;
    }
}
