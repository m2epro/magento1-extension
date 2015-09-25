<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Order_Change_Collection
    extends Ess_M2ePro_Model_Mysql4_Collection_Abstract
{
    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Order_Change');
    }

    // ########################################

    public function addAccountFilter($accountId)
    {
        $accountId = (int)$accountId;

        $this->getSelect()->join(
            array('mo' => Mage::getResourceModel('M2ePro/Order')->getMainTable()),
            '(`mo`.`id` = `main_table`.`order_id` AND `mo`.`account_id` = '.$accountId.')',
            array('account_id', 'marketplace_id')
        );
    }

    // ########################################

    public function addProcessingAttemptDateFilter($interval = 3600)
    {
        $interval = (int)$interval;

        if ($interval <= 0) {
            return;
        }

        $currentDate = new DateTime('now', new DateTimeZone('UTC'));
        $currentDate->modify("-{$interval} seconds");

        $this->getSelect()->where(
            'processing_attempt_date IS NULL OR processing_attempt_date <= ?', $currentDate->format('Y-m-d H:i:s')
        );
    }

    // ########################################
}