<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_5_0_6__v6_5_0_7_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->_installer;
        $connection = $installer->getConnection();

        $installer->getTableModifier('ebay_listing_product')
            ->addColumn('item_uuid', 'VARCHAR(32)', 'NULL', 'ebay_item_id', true)
            ->addColumn('is_duplicate', 'TINYINT(2) UNSIGNED NOT NULL', '0', 'item_uuid', true);

        // Amazon orders fulfilment details
        //########################################

        $installer->getSynchConfigModifier()->insert(
            '/amazon/orders/receive_details/', 'mode', 0, '0 - disable, \r\n1 - enable'
        );
        $installer->getSynchConfigModifier()->insert(
            '/amazon/orders/receive_details/', 'interval', 3600, 'in seconds'
        );
        $installer->getSynchConfigModifier()->insert(
            '/amazon/orders/receive_details/', 'last_time', NULL, 'Last check time'
        );

        // Grids Performance
        //########################################

        if (!$installer->getTablesObject()->isExists('indexer_listing_product_parent')) {

            $installer->run(<<<SQL

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_indexer_listing_product_parent')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_indexer_listing_product_parent')}` (
    `listing_product_id` INT(11) UNSIGNED NOT NULL,
    `listing_id` INT(11) UNSIGNED NOT NULL,
    `component_mode` VARCHAR(10) DEFAULT NULL,
    `min_price` DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
    `max_price` DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
    `create_date` DATETIME NOT NULL,
    PRIMARY KEY (`listing_product_id`),
    INDEX `listing_id` (`listing_id`),
    INDEX `component_mode` (`component_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
            );
        }

        // SUPPORT URLS CHANGES
        //########################################

        $installer->getMainConfigModifier()
            ->getEntity('/support/', 'knowledge_base_url')->updateValue('https://support.m2epro.com/knowledgebase');

        $installer->getMainConfigModifier()
            ->getEntity('/support/', 'documentation_url')->updateValue('https://docs.m2epro.com');

        $installer->getMainConfigModifier()
            ->getEntity('/support/', 'main_website_url')->updateValue('https://m2epro.com/');

        $installer->getMainConfigModifier()
            ->getEntity('/support/', 'main_support_url')->updateValue('https://support.m2epro.com/');

        $magentoConnectUrl = 'https://www.magentocommerce.com/magento-connect/'
            .'ebay-amazon-rakuten-magento-integration-order-import-and-stock-level-synchronization.html';
        $installer->getMainConfigModifier()
            ->getEntity('/support/', 'magento_connect_url')->updateValue($magentoConnectUrl);

        // AMAZON SHIPPING TEMPLATES
        //########################################

        if (!$installer->getTablesObject()->isExists('amazon_template_shipping_template')) {

            $installer->run(<<<SQL

DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_amazon_template_shipping_template')}`;
CREATE TABLE `{$this->_installer->getTable('m2epro_amazon_template_shipping_template')}` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `template_name` varchar(255) NOT NULL,
    `update_date` datetime DEFAULT NULL,
    `create_date` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `title` (`title`),
    INDEX `template_name` (`template_name`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
            );
        }

        $installer->getTableModifier('amazon_listing_product')
            ->addColumn('template_shipping_template_id', 'INT(11) UNSIGNED', 'NULL', 'template_description_id', true);

        $installer->getTableModifier('amazon_account')
            ->addColumn('shipping_mode', 'INT(11) UNSIGNED', '1', 'related_store_id');

        $installer->getTableModifier('amazon_template_synchronization')
            ->renameColumn('revise_change_shipping_override_template', 'revise_change_shipping_template', false);

        //----------------------------------------

        $installer->run(<<<SQL
    UPDATE `{$this->_installer->getTable('m2epro_amazon_account')}`
    SET `shipping_mode` = 0;
SQL
        );

        $tempTable = $installer->getTablesObject()->getFullName('listing_product');
        $queryStmt = $connection->query("
    SELECT `id`,
           `synch_reasons`
    FROM {$tempTable}
    WHERE `synch_reasons` LIKE '%shippingOverrideTemplate%';
");

        while ($row = $queryStmt->fetch()) {

            $reasons = explode(',', $row['synch_reasons']);
            $reasons =  array_unique(array_filter($reasons));

            array_walk($reasons, function (&$el){
                $el = str_replace('shippingOverrideTemplate', 'shippingTemplate', $el);
            });
            $reasons = implode(',', $reasons);

            $connection->query("
        UPDATE {$tempTable}
        SET `synch_reasons` = '{$reasons}'
        WHERE `id` = {$row['id']}
    ");
        }

        // AMAZON REPRICING
        //########################################

        $installer->getMainConfigModifier()->getEntity('/amazon/repricing/', 'mode')->updateValue(1);

        $installer->run(<<<SQL

TRUNCATE TABLE `{$this->_installer->getTable('m2epro_amazon_listing_product_repricing')}`;

UPDATE `{$this->_installer->getTable('m2epro_amazon_listing_other')}`
SET `is_repricing` = 0, `is_repricing_disabled` = 0;

UPDATE `{$this->_installer->getTable('m2epro_amazon_account_repricing')}`
SET `total_products` = 0, `last_checked_listing_product_update_date` = NULL;

SQL
        );

        $installer->getMainConfigModifier()->getEntity('/cron/task/repricing_synchronization/', 'last_run')
            ->updateValue(NULL);

        $installer->getMainConfigModifier()->updateGroup(
            '/cron/task/repricing_synchronization_general/',
            array('`group` = ?' => '/cron/task/repricing_synchronization/')
        );

        $installer->getMainConfigModifier()
            ->insert("/cron/task/repricing_synchronization_actual_price/", "mode", 1, "0 - disable,\r\n1 - enable");

        $installer->getMainConfigModifier()
            ->insert("/cron/task/repricing_synchronization_actual_price/", "interval", 3600, "in seconds");

        $installer->getMainConfigModifier()
            ->insert("/cron/task/repricing_synchronization_actual_price/", "last_run", NULL, "date of last access");

        $installer->getTableModifier('amazon_listing_product')
            ->addColumn('is_repricing', 'TINYINT(2) UNSIGNED NOT NULL', 0, 'online_qty', true);

        // ORDERS DOWNLOADING IMPROVEMENTS
        //########################################

        $amazonAccountTableName = $installer->getTablesObject()->getFullName('amazon_account');
        $result = $connection->query(<<<SQL
    SELECT aa.merchant_id,
           MIN(aa.orders_last_synchronization) as orders_last_synchronization
    FROM {$amazonAccountTableName} as aa
    WHERE aa.orders_last_synchronization IS NOT NULL
    GROUP BY aa.merchant_id
SQL
        )->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $item) {
            $installer->getSynchConfigModifier()->insert(
                "/amazon/orders/receive/{$item['merchant_id']}/",
                "from_update_date",
                $item['orders_last_synchronization']
            );
        }

        $installer->getTableModifier('amazon_account')->dropColumn('orders_last_synchronization');

        $installer->getSynchConfigModifier()->insert(
            '/amazon/orders/update/', 'interval', '1800', 'in seconds'
        );

        $installer->getTableModifier('ebay_account')->addColumn(
            'job_token', 'VARCHAR(255)', NULL, 'ebay_shipping_discount_profiles'
        );

        // OTHER SET OF CHANGES
        //########################################

        $installer->getMainConfigModifier()->insert(NULL, 'is_disabled', '0', '0 - disable, \r\n1 - enable');

        $installer->getMainConfigModifier()
            ->getEntity('/cron/service/', 'hostname')->updateKey('hostname_1');

        $installer->run(<<<SQL
DELETE FROM `{$this->_installer->getTable('m2epro_listing_other_log')}` 
WHERE `action` IN (2, 3, 9, 10, 11, 12, 13, 14, 15, 16, 17);
SQL
        );

        $installer->run(<<<SQL
    UPDATE `{$this->_installer->getTable('m2epro_marketplace')}`
    SET `url` = 'ebay.com/motors'
    WHERE `id` = 9;
SQL
        );

        $installer->getTableModifier('amazon_listing_product')
            ->changeColumn('is_afn_channel', 'TINYINT(2) UNSIGNED NOT NULL', 0);

        $listingTable = $installer->getTablesObject()->getFullName('listing');
        $listings = $installer->getConnection()->query("
  SELECT * FROM {$listingTable} WHERE `additional_data` LIKE '%mode_same_category_data%';
")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($listings as $listing) {

            $listingId = $listing['id'];
            $additionalData = (array)@json_decode($listing['additional_data'], true);

            if (!empty($additionalData['mode_same_category_data']['specifics'])) {
                foreach ($additionalData['mode_same_category_data']['specifics'] as &$specific) {
                    unset($specific['attribute_id'], $specific['mode_relation_id']);
                }
                unset($specific);
            }

            $connection->update(
                $listingTable,
                array('additional_data' => json_encode($additionalData)),
                array('id = ?' => $listingId)
            );
        }

        // REMOVE SOME RAKUTEN FEATURES
        //########################################

        $installer->getTableModifier('buy_account')
            ->dropColumn('ftp_new_sku_access')
            ->dropColumn('ftp_inventory_access')
            ->dropColumn('ftp_orders_access');

        $installer->getTableModifier('buy_listing_product')
            ->dropColumn('template_new_product_id');

        $installer->run(<<<SQL
DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_buy_dictionary_category')}`;
DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_buy_template_new_product')}`;
DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_buy_template_new_product_core')}`;
DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_buy_template_new_product_attribute')}`;
SQL
        );

        $installer->getMainConfigModifier()
            ->getEntity('/buy/template/new_sku/', 'upc_exemption')->delete();

        $tempTable = $installer->getTablesObject()->getFullName('wizard');
        $tempQuery = <<<SQL
SELECT * FROM {$tempTable}
WHERE `nick` = 'removedBuyNewSku';
SQL;
        $tempRow = $connection->query($tempQuery)->fetch();
        if ($tempRow === false) {

            $installer->run(<<<SQL
INSERT INTO `{$this->_installer->getTable('m2epro_wizard')}` (`nick`, `view`, `status`, `step`, `type`, `priority`)
SELECT 'removedBuyNewSku', 'common', 0, NULL, 0, MAX( `priority` )+1 FROM `m2epro_wizard`;
SQL
            );
        }

        $installer->run(<<<SQL
DELETE
  `mp`, `mpl`, `mcprs`, `mrps`
  FROM `{$this->_installer->getTable('m2epro_processing')}` `mp`
  LEFT JOIN `{$this->_installer->getTable('m2epro_processing_lock')}` `mpl` ON `mp`.`id` = `mpl`.`processing_id`
  LEFT JOIN `{$this->_installer->getTable('m2epro_connector_pending_requester_single')}` mcprs ON `mp`.`id` = `mcprs`.`processing_id`
  LEFT JOIN `{$this->_installer->getTable('m2epro_request_pending_single')}` `mrps` ON `mcprs`.`request_pending_single_id` = `mrps`.`id`
  WHERE `params` LIKE '%action_type":"new_sku"%'
SQL
        );

        $installer->getSynchConfigModifier()->getEntity('/buy/listings_products/update/', 'mode')->updateValue('0');
        $installer->getSynchConfigModifier()->getEntity('/buy/other_listings/update/', 'mode')->updateValue('0');

        $installer->run(<<<SQL
UPDATE `{$this->_installer->getTable('m2epro_buy_listing_other')}`
SET `title` = '--'
WHERE `title` IS NULL;

UPDATE `{$this->_installer->getTable('m2epro_listing_product')}` `mlp`
INNER JOIN `{$this->_installer->getTable('m2epro_buy_listing_product')}` `mblp` ON `mlp`.`id` = `mblp`.`listing_product_id`
SET `mlp`.`status` = 0
WHERE `mlp`.`status` != 0 AND `mblp`.`general_id` is NULL;
SQL
        );
    }

    //########################################
}