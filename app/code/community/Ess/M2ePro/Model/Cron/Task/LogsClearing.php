<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

final class Ess_M2ePro_Model_Cron_Task_LogsClearing extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'logs_clearing';
    const MAX_MEMORY_LIMIT = 128;

    const SYSTEM_LOG_MAX_DAYS = 30;
    const SYSTEM_LOG_MAX_RECORDS = 100000;

    //########################################

    protected function getNick()
    {
        return self::NICK;
    }

    protected function getMaxMemoryLimit()
    {
        return self::MAX_MEMORY_LIMIT;
    }

    //########################################

    protected function performActions()
    {
        /** @var $tempModel Ess_M2ePro_Model_Log_Clearing */
        $tempModel = Mage::getModel('M2ePro/Log_Clearing');

        $tempModel->clearOldRecords(Ess_M2ePro_Model_Log_Clearing::LOG_LISTINGS);
        $tempModel->clearOldRecords(Ess_M2ePro_Model_Log_Clearing::LOG_OTHER_LISTINGS);
        $tempModel->clearOldRecords(Ess_M2ePro_Model_Log_Clearing::LOG_SYNCHRONIZATIONS);
        $tempModel->clearOldRecords(Ess_M2ePro_Model_Log_Clearing::LOG_ORDERS);

        $this->clearSystemLog();

        return true;
    }

    //########################################

    private function clearSystemLog()
    {
        $this->clearSystemLogByAmount();
        $this->clearSystemLogByTime();
    }

    // ---------------------------------------

    private function clearSystemLogByAmount()
    {
        $resource = Mage::getSingleton('core/resource');
        $tableName = $resource->getTableName('m2epro_system_log');

        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');

        $counts = (int)$readConnection->select()
                                      ->from($tableName, array(new Zend_Db_Expr('COUNT(*)')))
                                      ->query()
                                      ->fetchColumn();

        if ($counts <= self::SYSTEM_LOG_MAX_RECORDS) {
            return;
        }

        $ids = $readConnection->select()
                              ->from($tableName, 'id')
                              ->limit($counts - self::SYSTEM_LOG_MAX_RECORDS)
                              ->order(array('id ASC'))
                              ->query()
                              ->fetchAll(Zend_Db::FETCH_COLUMN);

        $writeConnection->delete($tableName, 'id IN ('.implode(',',$ids).')');
    }

    private function clearSystemLogByTime()
    {
        $resource = Mage::getSingleton('core/resource');
        $tableName = $resource->getTableName('m2epro_system_log');
        $writeConnection = $resource->getConnection('core_write');

        $currentDate = Mage::helper('M2ePro')->getCurrentGmtDate();
        $dateTime = new DateTime($currentDate, new DateTimeZone('UTC'));
        $dateTime->modify('-'.self::SYSTEM_LOG_MAX_DAYS.' days');
        $minDate = $dateTime->format('Y-m-d 00:00:00');

        $writeConnection->delete($tableName,"create_date < '{$minDate}'");
    }

    //########################################
}