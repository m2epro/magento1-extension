<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

final class Ess_M2ePro_Model_Cron_Task_LogsClearing extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'logs_clearing';
    const MAX_MEMORY_LIMIT = 128;

    //####################################

    protected function getNick()
    {
        return self::NICK;
    }

    protected function getMaxMemoryLimit()
    {
        return self::MAX_MEMORY_LIMIT;
    }

    //####################################

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

    //####################################

    private function clearSystemLog()
    {
        $resource = Mage::getSingleton('core/resource');

        $tableName = $resource->getTableName('m2epro_system_log');

        $readConnection = $resource->getConnection('core_read');
        $counts = (int)$readConnection->select()
                                      ->from($tableName, array(new Zend_Db_Expr('COUNT(*)')))
                                      ->query()
                                      ->fetchColumn();

        $maxAllowedCount = 100000;
        if ($counts > $maxAllowedCount) {

            $ids = $readConnection->select()
                                  ->from($tableName, 'id')
                                  ->limit($counts - $maxAllowedCount)
                                  ->order(array('id ASC'))
                                  ->query()
                                  ->fetchAll(Zend_Db::FETCH_COLUMN);

            $resource->getConnection('core_write')->delete($tableName, 'id IN ('.implode(',',$ids).')');
        }

        $currentDate = Mage::helper('M2ePro')->getCurrentGmtDate();
        $dateTime = new DateTime($currentDate, new DateTimeZone('UTC'));
        $dateTime->modify('-30 days');
        $minDate = $dateTime->format('Y-m-d 00:00:00');

        $resource->getConnection('core_write')->delete($tableName,"create_date < '{$minDate}'");
    }

    //####################################
}