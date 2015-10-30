<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Log_Clearing
{
    const LOG_LISTINGS          = 'listings';
    const LOG_OTHER_LISTINGS    = 'other_listings';
    const LOG_SYNCHRONIZATIONS  = 'synchronizations';
    const LOG_ORDERS            = 'orders';

    //########################################

    public function clearOldRecords($log)
    {
        if (!$this->isValidLogType($log)) {
            return false;
        }

        $config = Mage::helper('M2ePro/Module')->getConfig();

        $mode = $config->getGroupValue('/logs/clearing/'.$log.'/','mode');
        $days = $config->getGroupValue('/logs/clearing/'.$log.'/','days');

        $mode = (int)$mode;
        $days = (int)$days;

        if ($mode != 1 || $days <= 0) {
            return false;
        }

        $minTime = $this->getMinTimeByDays($days);
        $this->clearLogByMinTime($log,$minTime);

        return true;
    }

    public function clearAllLog($log)
    {
        if (!$this->isValidLogType($log)) {
            return false;
        }

        $timestamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        $minTime = Mage::helper('M2ePro')->getDate($timestamp+60*60*24*365*10);

        $this->clearLogByMinTime($log,$minTime);

        return true;
    }

    // ---------------------------------------

    public function saveSettings($log, $mode, $days)
    {
        if (!$this->isValidLogType($log)) {
            return false;
        }

        $mode = (int)$mode;
        $days = (int)$days;

        if ($mode < 0 || $mode > 1) {
           $mode = 0;
        }

        if ($days <= 0) {
           $days = 90;
        }

        $config = Mage::helper('M2ePro/Module')->getConfig();

        $config->setGroupValue('/logs/clearing/'.$log.'/','mode', $mode);
        $config->setGroupValue('/logs/clearing/'.$log.'/','days', $days);

        return true;
    }

    //########################################

    private function isValidLogType($log)
    {
        return $log == self::LOG_LISTINGS ||
               $log == self::LOG_OTHER_LISTINGS ||
               $log == self::LOG_SYNCHRONIZATIONS ||
               $log == self::LOG_ORDERS;
    }

    private function getMinTimeByDays($days)
    {
        $timestamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        $dateTimeArray = getdate($timestamp);

        $hours = $dateTimeArray['hours'];
        $minutes = $dateTimeArray['minutes'];
        $seconds = $dateTimeArray['seconds'];
        $month = $dateTimeArray['mon'];
        $day = $dateTimeArray['mday'];
        $year = $dateTimeArray['year'];

        $timeStamp = mktime($hours,$minutes,$seconds,$month,$day - $days, $year);

        return Mage::helper('M2ePro')->getDate($timeStamp);
    }

    private function clearLogByMinTime($log, $minTime)
    {
        $table = NULL;

        switch($log) {
            case self::LOG_LISTINGS:
                $table = Mage::getResourceModel('M2ePro/Listing_Log')->getMainTable();
                break;
            case self::LOG_OTHER_LISTINGS:
                $table = Mage::getResourceModel('M2ePro/Listing_Other_Log')->getMainTable();
                break;
            case self::LOG_SYNCHRONIZATIONS:
                $table = Mage::getResourceModel('M2ePro/Synchronization_Log')->getMainTable();
                break;
            case self::LOG_ORDERS:
                $table = Mage::getResourceModel('M2ePro/Order_Log')->getMainTable();
                break;
        }

        if (is_null($table)) {
            return;
        }

        $where = array(' `create_date` < ? OR `create_date` IS NULL ' => (string)$minTime);

        /** @var $connWrite Varien_Db_Adapter_Pdo_Mysql */
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connWrite->delete($table,$where);
    }

    //########################################
}