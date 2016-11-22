<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Mysql4_Order_Change_Collection
    extends Ess_M2ePro_Model_Mysql4_Collection_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Order_Change');
    }

    //########################################

    public function addAccountFilter($accountId)
    {
        $accountId = (int)$accountId;

        $this->getSelect()->join(
            array('mo' => Mage::getResourceModel('M2ePro/Order')->getMainTable()),
            '(`mo`.`id` = `main_table`.`order_id` AND `mo`.`account_id` = '.$accountId.')',
            array('account_id', 'marketplace_id')
        );
    }

    //########################################

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

    //########################################

    public function addLockedObjectFilter($tag)
    {
        $mysqlTag = $this->getConnection()->quote($tag);
        $this->getSelect()->joinLeft(
            array('lo' => Mage::getResourceModel('M2ePro/LockedObject')->getMainTable()),
            '(`lo`.`object_id` = `main_table`.`order_id` AND `lo`.`tag` = '.$mysqlTag.')',
            array()
        );
        $this->getSelect()->where(
            '`lo`.`object_id` IS NULL'
        );
    }

    //########################################
}