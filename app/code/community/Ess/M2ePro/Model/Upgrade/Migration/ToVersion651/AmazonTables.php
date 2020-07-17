<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

// @codingStandardsIgnoreFile

class Ess_M2ePro_Model_Upgrade_Migration_ToVersion651_AmazonTables extends Ess_M2ePro_Model_Upgrade_Migration_Abstract
{
    //########################################

    public function run()
    {
        $this->marketplace();
        $this->account();
        $this->listings();
        $this->templates();
        $this->orders();
    }

    //########################################

    protected function marketplace()
    {
        $this->_installer
            ->getTableModifier('amazon_marketplace')
            ->addColumn(
                'is_business_available', 'tinyint(2) UNSIGNED NOT NULL', 0,
                'is_merchant_fulfillment_available', true, false
            )
            ->addColumn(
                'is_vat_calculation_service_available', 'tinyint(2) UNSIGNED NOT NULL', 0,
                'is_business_available', true, false
            )
            ->addColumn(
                'is_product_tax_code_policy_available', 'tinyint(2) UNSIGNED NOT NULL', 0,
                'is_vat_calculation_service_available', true, false
            )
            ->commit();

        $this->_installer->getConnection()->update(
            $this->_installer->getTablesObject()->getFullName('amazon_marketplace'),
            array('is_business_available' => 1),
            array('marketplace_id IN (?)' => array(23, 25, 28, 29, 26, 30, 31))
        );

        $this->_installer->getConnection()->update(
            $this->_installer->getTablesObject()->getFullName('amazon_marketplace'),
            array('is_vat_calculation_service_available' => 1),
            array('marketplace_id IN (?)' => array(25, 26, 28, 30, 31))
        );

        $this->_installer->getConnection()->update(
            $this->_installer->getTablesObject()->getFullName('amazon_marketplace'),
            array('is_product_tax_code_policy_available' => 1),
            array('marketplace_id IN (?)' => array(25, 28, 26, 30, 31))
        );

        $query = $this->_installer->getConnection()->query(
            "
SELECT * FROM {$this->getFullTableName('marketplace')} WHERE `id` IN (34, 35, 36)
        "
        );

        $rows = $query->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows)) {
            $this->_installer->run(
                <<<SQL

INSERT INTO `{$this->_installer->getTable('m2epro_marketplace')}` VALUES
  (34, 9, 'Mexico', 'MX', 'amazon.com.mx', 0, 10, 'America', 'amazon',
  '2017-09-27 00:00:00', '2017-09-27 00:00:00'),
  (35, 10, 'Australia', 'AU', 'amazon.com.au', 0, 11, 'Australia Region', 'amazon',
  '2017-09-27 00:00:00', '2017-09-27 00:00:00'),
  (36, 0, 'India', 'IN', 'amazon.in', 0, 12, 'Asia / Pacific', 'amazon',
  '2017-09-27 00:00:00', '2017-09-27 00:00:00');

INSERT INTO `{$this->_installer->getTable('m2epro_amazon_marketplace')}` VALUES
  (34, '8636-1433-4377', 'MXN',1,0,0,0,0),
  (35, '2770-5005-3793', 'AUD',1,0,0,0,0),
  (36, NULL, '',0,0,0,0,0);

SQL
            );
        } else {
            $this->_installer->getConnection()->update(
                $this->_installer->getFullTableName('marketplace'),
                array('group_title' => 'Australia Region'),
                array('id = ?' => 35)
            );
        }

        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName('amazon_marketplace'),
            array('is_new_asin_available' => 1),
            array('marketplace_id = ?' => 34)
        );

        $this->_installer
            ->getTableModifier('amazon_marketplace')
            ->addColumn(
                'is_automatic_token_retrieving_available', 'TINYINT(2) UNSIGNED NOT NULL', 0,
                'is_product_tax_code_policy_available'
            );

        $this->_installer->getConnection()->update(
            $this->getFullTableName('amazon_marketplace'),
            array('is_automatic_token_retrieving_available' => 1),
            array('marketplace_id IN (?)' => array(24, 25, 26, 28, 29, 30, 31, 34, 35))
        );
    }

    protected function account()
    {
        $this->_installer
            ->getTableModifier('amazon_account')
            ->addColumn(
                'is_vat_calculation_service_enabled', 'TINYINT(2) UNSIGNED NOT NULL', 0,
                'magento_orders_settings', false, false
            )
            ->addColumn(
                'is_magento_invoice_creation_disabled', 'TINYINT(2) UNSIGNED NOT NULL', 0,
                'is_vat_calculation_service_enabled', false, false
            )
            ->dropColumn('other_listings_move_settings', true, false)
            ->commit();
    }

    protected function listings()
    {
        $this->_installer
            ->getTableModifier('amazon_listing_product')
            ->dropColumn('template_shipping_override_id', true, false)
            ->addColumn(
                'template_shipping_id', 'INT(11) UNSIGNED', 'NULL', 'template_description_id',
                true, false
            )
            ->changeColumn('is_afn_channel', 'TINYINT(2) UNSIGNED NOT NULL', 0, null, false)
            ->changeColumn('search_settings_data', 'LONGTEXT', 'NULL', null, false)
            ->addColumn(
                'variation_parent_afn_state', 'SMALLINT(4) UNSIGNED', 'NULL',
                'is_general_id_owner', true, false
            )
            ->addColumn(
                'variation_parent_repricing_state', 'SMALLINT(4) UNSIGNED', 'NULL',
                'variation_parent_afn_state', true, false
            )
            ->renameColumn(
                'online_price', 'online_regular_price', true, false
            )
            ->renameColumn(
                'online_sale_price', 'online_regular_sale_price', true, false
            )
            ->renameColumn(
                'online_sale_price_start_date', 'online_regular_sale_price_start_date', true, false
            )
            ->renameColumn(
                'online_sale_price_end_date', 'online_regular_sale_price_end_date', true, false
            )
            ->addColumn(
                'template_product_tax_code_id', 'INT(11) UNSIGNED', 'NULL', 'template_shipping_id', true, false
            )
            ->addColumn(
                'online_business_price', 'DECIMAL(12, 4) UNSIGNED', 'NULL',
                'online_regular_sale_price_end_date', true, false
            )
            ->addColumn(
                'online_business_discounts', 'TEXT', 'NULL', 'online_business_price', false, false
            )
            ->addColumn('online_handling_time', 'INT(11) UNSIGNED', 'NULL', 'online_qty', false, false)
            ->addColumn('online_restock_date', 'DATETIME', 'NULL', 'online_handling_time', false, false)
            ->addColumn('online_details_data', 'LONGTEXT', 'NULL', 'online_restock_date', false, false)
            ->addColumn('online_images_data', 'LONGTEXT', 'NULL', 'online_details_data', false, false)
            ->commit();

        $accountsTable = $this->getFullTableName('account');
        $lockItemTable = $this->getFullTableName('lock_item');
        $amazonProcessingListSkuTable = $this->getFullTableName('amazon_listing_product_action_processing_list_sku');

        $skuQueueLockItemsStmt = $this->_installer->getConnection()->query(
            "
SELECT * FROM {$lockItemTable} WHERE `nick` LIKE 'amazon_list_skus_queue_%';
"
        );

        $amazonAccountsIds = $this->_installer->getConnection()->query(
            "
SELECT `id` FROM {$accountsTable} WHERE `component_mode` = 'amazon';
"
        )->fetchAll(PDO::FETCH_COLUMN);

        $lockItemsIds = array();

        while ($lockItemData = $skuQueueLockItemsStmt->fetch(PDO::FETCH_ASSOC)) {
            $lockItemsIds[] = $lockItemData['id'];

            $accountId = str_replace('amazon_list_skus_queue_', '', $lockItemData['nick']);
            if (!in_array($accountId, $amazonAccountsIds)) {
                continue;
            }

            $skus = @json_decode($lockItemData['data'], true);
            if (empty($skus)) {
                continue;
            }

            $insertData = array();

            foreach (array_unique($skus) as $sku) {
                $insertData[] = array(
                    'account_id'  => $accountId,
                    'sku'         => $sku,
                    'create_date' => date('Y-m-d H:i:s', gmdate('U'))
                );
            }

            $this->_installer->getConnection()->insertMultiple(
                $amazonProcessingListSkuTable, $insertData
            );
        }

        if (!empty($lockItemsIds)) {
            $this->_installer->getConnection()->delete(
                $lockItemTable,
                array('id IN (?)' => array_unique($lockItemsIds))
            );
        }

        $this->_installer
            ->getTableModifier('amazon_listing_product_repricing')
            ->changeColumn('is_online_disabled', 'TINYINT(2) UNSIGNED NOT NULL', '0', null, false)
            ->addColumn(
                'is_online_inactive', 'TINYINT(2) UNSIGNED NOT NULL', '0', 'is_online_disabled', true, false
            )
            ->addColumn(
                'last_updated_regular_price', 'DECIMAL(12, 4) UNSIGNED', 'NULL', 'online_max_price', false,
                false
            )
            ->addColumn(
                'last_updated_min_price', 'DECIMAL(12, 4) UNSIGNED', 'NULL', 'last_updated_regular_price',
                false, false
            )
            ->addColumn(
                'last_updated_max_price', 'DECIMAL(12, 4) UNSIGNED', 'NULL', 'last_updated_min_price', false, false
            )
            ->addColumn(
                'last_updated_is_disabled', 'TINYINT(2) UNSIGNED', 'NULL', 'last_updated_max_price', false, false
            )
            ->commit();

        $this->_installer
            ->getTableModifier('amazon_listing_other')
            ->addColumn(
                'is_repricing_inactive', 'TINYINT(2) UNSIGNED NOT NULL', '0', 'is_repricing_disabled',
                true, false
            )
            ->commit();

        $this->_installer->run(
            <<<SQL
UPDATE `{$this->_installer->getTable('m2epro_amazon_listing_product_repricing')}`
SET `is_online_inactive` = 0;

UPDATE `{$this->_installer->getTable('m2epro_amazon_listing_other')}`
SET `is_repricing_inactive` = 0;

UPDATE `{$this->_installer->getTable('m2epro_listing_product')}` mlp
JOIN `{$this->_installer->getTable('m2epro_amazon_listing_product')}` malp ON mlp.id = malp.listing_product_id
SET mlp.additional_data = REPLACE(`additional_data`, 'repricing_disabled_count', 'repricing_not_managed_count')
WHERE malp.is_repricing = 1;

UPDATE `{$this->_installer->getTable('m2epro_listing_product')}` mlp
JOIN `{$this->_installer->getTable('m2epro_amazon_listing_product')}` malp ON mlp.id = malp.listing_product_id
SET mlp.additional_data = REPLACE(`additional_data`, 'repricing_enabled_count', 'repricing_managed_count')
WHERE malp.is_repricing = 1;
SQL
        );

    }

    protected function templates()
    {
        $this->_installer
            ->getTableModifier('amazon_template_synchronization')
            ->dropColumn('relist_send_data', true, false)
            ->dropColumn('revise_change_description_template', true, false)
            ->dropColumn('revise_change_product_tax_code_template', true, false)
            ->dropColumn('revise_change_shipping_override_template', true, false)
            ->addColumn(
                'stop_mode', 'TINYINT(2) UNSIGNED NOT NULL', null, 'relist_qty_calculated_value_max', false, false
            )
            ->commit();

        $this->_installer->run(
            <<<SQL
UPDATE `{$this->_installer->getTable('m2epro_amazon_template_synchronization')}`
SET `stop_mode` = 1
WHERE (`stop_status_disabled`+`stop_out_off_stock`+`stop_qty_magento`+`stop_qty_calculated`) > 0;
SQL
        );

        $this->_installer->getTableModifier('amazon_template_selling_format')
                         ->renameColumn('price_vat_percent', 'regular_price_vat_percent');

        $this->_installer
            ->getTableModifier('amazon_template_selling_format')
            ->changeColumn('regular_price_vat_percent', 'FLOAT UNSIGNED', 'NULL', null, false)
            ->renameColumn(
                'price_mode', 'regular_price_mode', false, false
            )
            ->renameColumn(
                'price_custom_attribute', 'regular_price_custom_attribute', false, false
            )
            ->renameColumn(
                'price_coefficient', 'regular_price_coefficient', false, false
            )
            ->renameColumn(
                'map_price_mode', 'regular_map_price_mode', false, false
            )
            ->renameColumn(
                'map_price_custom_attribute', 'regular_map_price_custom_attribute', false, false
            )
            ->renameColumn(
                'sale_price_mode', 'regular_sale_price_mode', false, false
            )
            ->renameColumn(
                'sale_price_custom_attribute', 'regular_sale_price_custom_attribute', false, false
            )
            ->renameColumn(
                'sale_price_coefficient', 'regular_sale_price_coefficient', false, false
            )
            ->renameColumn(
                'price_variation_mode', 'regular_price_variation_mode', false, false
            )
            ->renameColumn(
                'sale_price_start_date_mode', 'regular_sale_price_start_date_mode', false, false
            )
            ->renameColumn(
                'sale_price_start_date_value', 'regular_sale_price_start_date_value', false, false
            )
            ->renameColumn(
                'sale_price_start_date_custom_attribute', 'regular_sale_price_start_date_custom_attribute', false, false
            )
            ->renameColumn(
                'sale_price_end_date_mode', 'regular_sale_price_end_date_mode', false, false
            )
            ->renameColumn(
                'sale_price_end_date_value', 'regular_sale_price_end_date_value', false, false
            )
            ->renameColumn(
                'sale_price_end_date_custom_attribute', 'regular_sale_price_end_date_custom_attribute', false, false
            )
            ->addColumn(
                'is_regular_customer_allowed', 'TINYINT(2) UNSIGNED NOT NULL', 1, 'qty_max_posted_value', false, false
            )
            ->addColumn(
                'is_business_customer_allowed', 'TINYINT(2) UNSIGNED NOT NULL', 0,
                'is_regular_customer_allowed', false, false
            )
            ->addColumn(
                'business_price_mode', 'TINYINT(2) UNSIGNED NOT NULL', null,
                'regular_price_vat_percent', false, false
            )
            ->addColumn(
                'business_price_custom_attribute', 'VARCHAR(255) NOT NULL', null,
                'business_price_mode', false, false
            )
            ->addColumn(
                'business_price_coefficient', 'VARCHAR(255) NOT NULL', null,
                'business_price_custom_attribute', false, false
            )
            ->addColumn(
                'business_price_variation_mode', 'TINYINT(2) UNSIGNED NOT NULL',
                null, 'business_price_coefficient', false, false
            )
            ->addColumn(
                'business_price_vat_percent', 'FLOAT UNSIGNED', 'NULL',
                'business_price_variation_mode', false, false
            )
            ->addColumn(
                'business_discounts_mode', 'TINYINT(2) UNSIGNED NOT NULL',
                null, 'business_price_vat_percent', false, false
            )
            ->addColumn(
                'business_discounts_tier_coefficient', 'VARCHAR(255) NOT NULL',
                null, 'business_discounts_mode', false, false
            )
            ->addColumn(
                'business_discounts_tier_customer_group_id', 'INT(11) UNSIGNED',
                'NULL', 'business_discounts_tier_coefficient', false, false
            )
            ->dropIndex('price_variation_mode', false)
            ->commit();

        $this->_installer
            ->getTableModifier('amazon_template_description_definition')
            ->addColumn('msrp_rrp_mode', 'TINYINT(2) UNSIGNED', '0', 'number_of_items_custom_attribute', false, false)
            ->addColumn('msrp_rrp_custom_attribute', 'VARCHAR(255)', null, 'msrp_rrp_mode', false, false)
            ->commit();
    }

    protected function orders()
    {
        $this->_installer
            ->getTableModifier('amazon_order')
            ->addColumn('is_business', 'TINYINT(2) UNSIGNED NOT NULL', '0', 'is_prime', true, false)
            ->addColumn('seller_order_id', 'VARCHAR(255)', 'NULL', 'amazon_order_id', true, false)
            ->addIndex('purchase_create_date', false)
            ->commit();
    }

    //########################################
}
