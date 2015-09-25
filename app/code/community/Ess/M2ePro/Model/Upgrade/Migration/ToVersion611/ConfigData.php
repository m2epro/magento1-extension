<?php

/*
 * @copyright  Copyright (c) 2014 by  ESS-UA.
 */

class Ess_M2ePro_Model_Upgrade_Migration_ToVersion611_ConfigData
{
    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    private $installer = NULL;

    //####################################

    public function getInstaller()
    {
        return $this->installer;
    }

    public function setInstaller(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer)
    {
        $this->installer = $installer;
    }

    //####################################

    /*
        DELETE FROM `m2epro_cache_config` WHERE (`group` = '/servicing/' AND `key` = 'cron_interval');

        DELETE FROM `m2epro_config` WHERE ((`group` = '/cron/' AND `key` = 'double_run_protection')
            OR (`group` LIKE '/logs/cleaning/%' AND `key` = 'default')
            OR (`group` = '/view/ebay/cron/popup/') OR (`group` = '/view/common/cron/error/')
            OR (`group` = '/view/ebay/cron/notification/')
            OR (`group` = '/listings/lockItem/' AND `key` = 'max_deactivate_time'));

        UPDATE `m2epro_config` SET `value` = NULL WHERE (`group` LIKE '/cron/%'
            AND (`key` = 'last_access' OR `key` = 'last_run'));

        UPDATE `m2epro_config` SET `value` = '53200' WHERE (`group` = '/cron/task/servicing/' AND `key` = 'interval');

        INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
        ('/cron/', 'type', 'service', NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00'),
        ('/cron/task/logs_cleaning/', 'last_run', NULL, NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00'),
        ('/cron/task/processing/', 'last_run', NULL, NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00'),
        ('/cron/task/servicing/', 'last_run', NULL, NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00'),
        ('/cron/task/synchronization/', 'last_run', NULL, NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00'),
        ('/cron/service/', 'endpoint', 'http://cron.m2epro.com/', NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00'),
        ('/cron/service/', 'auth_key', NULL, NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00');

        INSERT INTO `m2epro_synchronization_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
        (NULL, 'la  st_access', NULL, NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00'),
        (NULL, 'last_run', NULL, NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00');

        DELETE FROM `m2epro_synchronization_config` WHERE ((`key` = 'max_deactivate_time')
            OR (`group` = '/feedbacks/')
            OR (`group` = '/marketplaces/')
            OR (`group` = '/orders/')
            OR (`group` = '/other_listings/')
            OR (`group` = '/policies/')
            OR (`group` = '/templates/')
            OR (`group` = '/defaults/processing/')
            OR (`group` = '/settings/profiler/'));

        UPDATE `m2epro_synchronization_config` SET `key` = 'type'
            WHERE (`group` = '/defaults/inspector/product_changes/' AND `key` = 'mode');

        UPDATE `m2epro_synchronization_config` SET `key` = 'last_time'
            WHERE (`key` = 'last_access' AND (`group` = '/amazon/orders/reserve_cancellation/'
                OR `group` = '/ebay/feedbacks/receive/'
                OR `group` = '/ebay/feedbacks/response/'
                OR `group` = '/ebay/orders/cancellation/'
                OR `group` = '/ebay/orders/reserve_cancellation/'));
     */

    //####################################

    public function process()
    {
        $this->processCacheConfigTable();
        $this->processConfigTable();
        $this->processSynchronizationConfigTable();
    }

    //####################################

    private function processCacheConfigTable()
    {
        $connection = $this->installer->getConnection();
        $tempTable = $this->installer->getTable('m2epro_cache_config');

        $connection->delete($tempTable, "`group` = '/servicing/' AND `key` = 'cron_interval'");
    }

    private function processConfigTable()
    {
        $connection = $this->installer->getConnection();
        $tempTable = $this->installer->getTable('m2epro_config');

        $tempQuery = "SELECT * FROM `{$tempTable}` WHERE `group` = '/cron/' AND `key` = 'type'";
        $tempRow = $connection->query($tempQuery)->fetch();

        if ($tempRow === false) {

            $this->installer->run(<<<SQL

INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/cron/', 'type', 'magento', NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00'),
('/cron/task/logs_cleaning/', 'last_run', NULL, 'date of last run', '2014-01-01 00:00:00', '2014-01-01 00:00:00'),
('/cron/task/processing/', 'last_run', NULL, 'date of last run', '2014-01-01 00:00:00', '2014-01-01 00:00:00'),
('/cron/task/servicing/', 'last_run', NULL, 'date of last run', '2014-01-01 00:00:00', '2014-01-01 00:00:00'),
('/cron/task/synchronization/', 'last_run', NULL, 'date of last run', '2014-01-01 00:00:00', '2014-01-01 00:00:00'),
('/cron/service/', 'auth_key', NULL, NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00');

SQL
);
        }

        $where = "(`group` = '/cron/' AND `key` = 'double_run_protection') ";
        $where .= "OR (`group` LIKE '/logs/cleaning/%' AND `key` = 'default') ";
        $where .= "OR (`group` = '/view/ebay/cron/popup/') ";
        $where .= "OR (`group` = '/view/common/cron/error/') ";
        $where .= "OR (`group` = '/view/ebay/cron/notification/') ";
        $where .= "OR (`group` = '/listings/lockItem/' AND `key` = 'max_deactivate_time')";

        $connection->delete($tempTable, $where);

        $connection->update(
            $tempTable, array('value' => NULL),
            "`group` LIKE '/cron/%' AND (`key` = 'last_access' OR `key` = 'last_run')"
        );

        $interval = rand(43200, 86400);
        $connection->update(
            $tempTable, array('value' => $interval), "`group` = '/cron/task/servicing/' AND `key` = 'interval'"
        );
    }

    private function processSynchronizationConfigTable()
    {
        $connection = $this->installer->getConnection();
        $tempTable = $this->installer->getTable('m2epro_synchronization_config');

        $tempQuery = "SELECT * FROM `{$tempTable}` WHERE `group` IS NULL AND `key` = 'last_access'";
        $tempRow = $connection->query($tempQuery)->fetch();

        if ($tempRow === false) {

            $this->installer->run(<<<SQL

INSERT INTO `m2epro_synchronization_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
(NULL, 'last_access', NULL, NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00'),
(NULL, 'last_run', NULL, NULL, '2014-01-01 00:00:00', '2014-01-01 00:00:00');

SQL
);
        }

        $where = "(`key` = 'max_deactivate_time') ";
        $where .= "OR (`group` = '/feedbacks/') ";
        $where .= "OR (`group` = '/marketplaces/') ";
        $where .= "OR (`group` = '/orders/') ";
        $where .= "OR (`group` = '/other_listings/') ";
        $where .= "OR (`group` = '/policies/') ";
        $where .= "OR (`group` = '/templates/') ";
        $where .= "OR (`group` = '/defaults/processing/') ";
        $where .= "OR (`group` = '/settings/profiler/')";

        $connection->delete($tempTable, $where);

        $connection->update(
            $tempTable, array('key' => 'type'),
            "`group` = '/defaults/inspector/product_changes/' AND `key` = 'mode'"
        );

        $subWhere = "`group` = '/amazon/orders/reserve_cancellation/' ";
        $subWhere .= "OR `group` = '/ebay/feedbacks/receive/' ";
        $subWhere .= "OR `group` = '/ebay/feedbacks/response/' ";
        $subWhere .= "OR `group` = '/ebay/orders/cancellation/' ";
        $subWhere .= "OR `group` = '/ebay/orders/reserve_cancellation/'";

        $connection->update(
            $tempTable, array('key' => 'last_time'),
            "`key` = 'last_access' AND ({$subWhere})"
        );
    }

    //####################################
}