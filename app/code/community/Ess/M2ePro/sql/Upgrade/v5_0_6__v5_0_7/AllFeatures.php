<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v5_0_6__v5_0_7_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->_installer;
        $connection = $installer->getConnection();

        $installer->run(<<<SQL

UPDATE `m2epro_config`
SET `group` = '/product/index/cataloginventory_stock/'
WHERE `group` = '/cache/index/cataloginventory_stock/';

UPDATE `m2epro_config`
SET `group` = '/product/index/catalog_product_attribute/'
WHERE `group` = '/cache/index/catalog_product_attribute/';

UPDATE `m2epro_config`
SET `group` = '/product/index/catalog_product_price/'
WHERE `group` = '/cache/index/catalog_product_price/';

SQL
        );

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_order_change');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'hash') === false) {
            $connection->addColumn($tempTable, 'hash',
                                   'VARCHAR(50) DEFAULT NULL AFTER `creator_type`');
        }

        if (!isset($tempTableIndexList[strtoupper('hash')])) {
            $connection->addKey($tempTable, 'hash', 'hash');
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_listing');

        if ($connection->tableColumnExists($tempTable, 'products_listed_count') !== false &&
            $connection->tableColumnExists($tempTable, 'products_active_count') === false) {

            $connection->changeColumn(
                $tempTable,
                'products_listed_count',
                'products_active_count',
                'INT(11) UNSIGNED NOT NULL DEFAULT 0'
            );
        }

        if ($connection->tableColumnExists($tempTable, 'items_active_count') === false) {
            $connection->addColumn($tempTable, 'items_active_count',
                                   'INT(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `products_inactive_count`');
        }

        // ---------------------------------------

        $tempTable = $installer->getTable('m2epro_ebay_listing');
        $tempTableIndexList = $connection->getIndexList($tempTable);

        if ($connection->tableColumnExists($tempTable, 'items_sold_count') === false) {
            $connection->addColumn($tempTable, 'items_sold_count',
                                   'INT(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `products_sold_count`');
        }

        if (!isset($tempTableIndexList[strtoupper('items_sold_count')])) {
            $connection->addKey($tempTable, 'items_sold_count', 'items_sold_count');
        }

        //########################################

        $tempTable = $installer->getTable('m2epro_amazon_account');
        $tempAccounts = $connection->query("SELECT * FROM `{$tempTable}`")->fetchAll();

        foreach ($tempAccounts as $account) {

            if (empty($account['magento_orders_settings'])) {
                continue;
            }

            $ordersSettings = (array)json_decode($account['magento_orders_settings'], true);

            if (!isset($ordersSettings['fba_stock_mode'])) {
                continue;
            }

            // Ess_M2ePro_Model_Amazon_Account::MAGENTO_ORDERS_FBA_MODE_YES
            $fbaMode = 1;
            $fbaStockMode = (int)$ordersSettings['fba_stock_mode'];

            unset($ordersSettings['fba_stock_mode']);

            $ordersSettings['fba'] = array(
                'mode' => $fbaMode,
                'stock_mode' => $fbaStockMode
            );
            $ordersSettings = $connection->quote(json_encode($ordersSettings));

            $connection->query(
                "UPDATE `{$tempTable}`
                 SET `magento_orders_settings` = {$ordersSettings}
                 WHERE `account_id` = ".(int)$account['account_id']
            );
        }

        //########################################

        $tempTable = $installer->getTable('m2epro_config');
        $tempRow = $connection->query("
            SELECT * FROM `{$tempTable}`
            WHERE `group` = '/amazon/synchronization/settings/orders/update/'
            AND   `key` = 'mode'
        ")->fetch();

        if ($tempRow === false) {

            $installer->run(<<<SQL

INSERT INTO m2epro_config (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/amazon/order/settings/marketplace_25/', 'use_first_street_line_as_company', '1', '0 - disable, \r\n1 - enable',
 '2013-04-17 00:00:00', '2013-04-17 00:00:00'),
('/ebay/order/settings/marketplace_8/', 'use_first_street_line_as_company', '1', '0 - disable, \r\n1 - enable',
 '2013-04-17 00:00:00', '2013-04-17 00:00:00'),
('/amazon/synchronization/settings/orders/update/', 'mode', '1', 'in seconds',
 '2013-04-19 00:00:00', '2013-04-19 00:00:00');

SQL
            );
        }
    }

    //########################################
}