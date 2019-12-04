<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Request_Pending_Single_Collection
    extends Ess_M2ePro_Model_Resource_Collection_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Request_Pending_Single');
    }

    //########################################

    public function setOnlyExpiredItemsFilter()
    {
        $this->addFieldToFilter('expiration_date', array('lt' => Mage::helper('M2ePro')->getCurrentGmtDate()));
        return $this;
    }

    public function setOnlyOutdatedItemsFilter()
    {
        $interval = new \DateTime('now', new \DateTimeZone('UTC'));
        $interval->modify('-12 hours');

        $this->addFieldToFilter('expiration_date', array('lt' => $interval->format('Y-m-d H:i:s')));
        return $this;
    }

    //########################################
}
