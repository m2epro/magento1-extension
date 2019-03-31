<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Mysql4_Listing_Product_ScheduledAction_Collection
    extends Ess_M2ePro_Model_Mysql4_Collection_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Listing_Product_ScheduledAction');
    }

    //########################################

    public function addCreatedBeforeFilter($secondsInterval)
    {
        $interval = new \DateTime('now', new \DateTimeZone('UTC'));
        $interval->modify("-{$secondsInterval} seconds");

        $this->addFieldToFilter('main_table.create_date', array('lt' => $interval->format('Y-m-d H:i:s')));
        return $this;
    }

    //########################################
}