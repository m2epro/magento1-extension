<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_3_6__v6_3_7_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->_installer;
        $connection = $installer->getConnection();

        $installer->getTableModifier('ebay_account')
            ->addColumn('defaults_last_synchronization', 'datetime NULL', 'NULL', 'marketplaces_data');

        // ---------------------------------------

        $installer->getTableModifier('amazon_listing_other')
            ->changeColumn('online_qty', 'INT(11) UNSIGNED NULL', 'NULL');

        // ---------------------------------------

        $installer->getTableModifier('ebay_template_description')
            ->changeColumn('variation_configurable_images', 'TEXT NULL', 'NULL');

        // ---------------------------------------

        $installer->getTableModifier('order_log')
            ->addColumn('update_date', 'datetime NULL', 'NULL', 'additional_data', false, false)
            ->renameColumn('message', 'description', false, false)
            ->commit();

        $installer->getTableModifier('order_log')
            ->changeColumn('description', 'TEXT NULL', 'NULL');

        // ---------------------------------------

        $installer->getTableModifier('amazon_template_selling_format')
            ->addColumn('price_vat_percent', 'FLOAT UNSIGNED NOT NULL', 0,
                        'sale_price_end_date_custom_attribute');

        // ---------------------------------------

        $installer->getTableModifier('buy_template_selling_format')
            ->addColumn('price_vat_percent', 'FLOAT UNSIGNED NOT NULL', 0,
                        'price_variation_mode');

        // ---------------------------------------

        $installer->getTableModifier('listing')
            ->addColumn('auto_global_adding_add_not_visible', 'TINYINT(2) UNSIGNED NOT NULL', 1,
                        'auto_global_adding_mode');

        // ---------------------------------------

        $installer->getTableModifier('listing')
            ->addColumn('auto_website_adding_add_not_visible', 'TINYINT(2) UNSIGNED NOT NULL', 1,
                        'auto_website_adding_mode');

        // ---------------------------------------

        $installer->getTableModifier('listing_auto_category_group')
            ->addColumn('adding_add_not_visible', 'TINYINT(2) UNSIGNED NOT NULL', 1,
                        'adding_mode');

        // ---------------------------------------

        if ($installer->getTablesObject()->isExists('ebay_dictionary_motor_specific')) {

            $installer->run(<<<SQL
DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_ebay_dictionary_motor_epid')}`;
SQL
            );

            $this->_installer->getTablesObject()->renameTable(
                'm2epro_ebay_dictionary_motor_specific',
                'm2epro_ebay_dictionary_motor_epid'
            );
        }

        // ---------------------------------------

        if (!$installer->getTablesObject()->isExists('ebay_motor_filter')) {

            $installer->run(<<<SQL
CREATE TABLE `{$this->_installer->getTable('m2epro_ebay_motor_filter')}` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `type` TINYINT(2) UNSIGNED NOT NULL,
    `conditions` TEXT NOT NULL,
    `note` TEXT DEFAULT NULL,
    `update_date` datetime DEFAULT NULL,
    `create_date` datetime DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX type (`type`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;
SQL
            );
        }

// ---------------------------------------

        if (!$installer->getTablesObject()->isExists('ebay_motor_group')) {

            $installer->run(<<<SQL
CREATE TABLE `{$this->_installer->getTable('m2epro_ebay_motor_group')}` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `mode` TINYINT(2) UNSIGNED NOT NULL,
    `type` TINYINT(2) UNSIGNED NOT NULL,
    `items_data` TEXT DEFAULT NULL,
    `update_date` datetime DEFAULT NULL,
    `create_date` datetime DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX mode (`mode`),
    INDEX type (`type`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;
SQL
            );
        }

        // ---------------------------------------

        if (!$installer->getTablesObject()->isExists('ebay_motor_filter_to_group')) {

            $installer->run(<<<SQL
CREATE TABLE `{$this->_installer->getTable('m2epro_ebay_motor_filter_to_group')}` (
    id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `filter_id` INT(11) UNSIGNED NOT NULL,
    `group_id` INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    INDEX filter_id (`filter_id`),
    INDEX group_id (`group_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;
SQL
            );
        }

        // ---------------------------------------

        $installer->getTableModifier('buy_template_new_product_core')
            ->dropColumn('asin_mode', false, false)
            ->dropColumn('asin_custom_attribute', false, false)
            ->commit();

        // ---------------------------------------

        $installer->getTableModifier('buy_account')
            ->dropColumn('other_listings_update_titles_settings', false, false)
            ->dropColumn('other_listings_first_synchronization', false, false)
            ->commit();

        // ---------------------------------------

        $installer->getTableModifier('ebay_listing_product_variation')
            ->addColumn('online_sku', 'VARCHAR(255) NULL', 'NULL', 'delete', true);

        // ---------------------------------------

        $installer->getTableModifier('ebay_account')
            ->renameColumn('ebay_info', 'info', false, false)
            ->addColumn('user_id', 'VARCHAR(255) NOT NULL', NULL, 'server_hash', false, false)
            ->commit();

        // --------------------------------------------

        $installer->getTableModifier('buy_account')
            ->addColumn('seller_id', 'VARCHAR(255) NULL', 'NULL', 'server_hash', false);

        //########################################

        $tempConfigEntity = $installer->getSynchConfigModifier()
            ->getEntity('/ebay/defaults/update_listings_products/',
                        'since_time');

        if ($tempConfigEntity->isExists()) {

            $tempValue = $tempConfigEntity->getValue();
            $sinceTime = !$tempValue ? 'NULL' : $connection->quote($tempValue);

            $tempConfigEntity->delete();

            $installer->run(<<<SQL
UPDATE `{$this->_installer->getTable('m2epro_ebay_account')}`
SET `defaults_last_synchronization` = {$sinceTime}
SQL
            );
        }

        //########################################

        $installer->getMainConfigModifier()->getEntity('/cron/', 'type')->updateKey('runner');
        $installer->getMainConfigModifier()->getEntity('/cron/', 'last_type_change')->updateKey('last_runner_change');
        $installer->getMainConfigModifier()->getEntity('/cron/', 'last_executed_slow_task')->insert(null);

        //########################################

        $mcModifier = $installer->getMainConfigModifier();
        $mcModifier->delete('/cron/task/processing/');

        $scModifier = $installer->getSynchConfigModifier();
        $scModifier->insert('/defaults/processing/', 'mode', 1);

        $installer->run(<<<SQL

DELETE FROM `{$this->_installer->getTable('m2epro_lock_item')}`
WHERE `nick` = 'cron'
OR    `nick` = 'processing_cron'

SQL
        );

        //########################################

        $installer->getMainConfigModifier()
            ->updateGroup('/ebay/motors/', array('`group` = ?' => '/ebay/motor/'));
        $installer->getMainConfigModifier()
            ->getEntity('/ebay/motors/','motors_specifics_attribute')->updateKey('epids_attribute');
        $installer->getMainConfigModifier()
            ->getEntity('/ebay/motors/','motors_ktypes_attribute')->updateKey('ktypes_attribute');

        $installer->getMainConfigModifier()->updateGroup(
            '/view/ebay/motors_epids_attribute/',
            array('`group` = ?' => '/view/ebay/motors_specifics_attribute/')
        );

        $installer->getSynchConfigModifier()->updateGroup(
            '/ebay/marketplaces/motors_epids/',
            array('`group` = ?' => '/ebay/marketplaces/motors_specifics/')
        );

        //########################################

        $installer->getSynchConfigModifier()
            ->delete('/buy/other_listings/title/', 'mode');

        //########################################

        $installer->run(<<<SQL

    UPDATE `{$this->_installer->getTable('m2epro_ebay_template_selling_format')}`
    SET `fixed_price_mode` = 1
    WHERE `listing_type` = 2 AND `fixed_price_mode` = 0;

    UPDATE `{$this->_installer->getTable('m2epro_amazon_listing_other')}`
    SET `online_qty` = NULL
    WHERE `is_afn_channel` = 1 AND `online_qty` IS NOT NULL;

    UPDATE `{$this->_installer->getTable('m2epro_amazon_listing_other')}`
    SET `online_qty` = 0
    WHERE `is_afn_channel` = 0 AND `online_qty` IS NULL;

    UPDATE `{$this->_installer->getTable('m2epro_ebay_template_description')}`
    SET `variation_configurable_images` = '[]'
    WHERE LENGTH(`variation_configurable_images`) = 0;

    UPDATE `{$this->_installer->getTable('m2epro_ebay_template_description')}`
    SET `variation_configurable_images` = CONCAT('["', `variation_configurable_images`, '"]')
    WHERE `variation_configurable_images` NOT LIKE '[%';

    UPDATE `{$this->_installer->getTable('m2epro_buy_listing')}`
    SET `general_id_mode` = 0,
        `general_id_custom_attribute` = ''
    WHERE `general_id_mode` NOT IN(0,1);

    UPDATE `{$this->_installer->getTable('m2epro_listing_product')}`
    SET `additional_data` = CONCAT(
                                SUBSTRING(`additional_data`,
                                          1,
                                          INSTR(`additional_data`, '"ebay_product_images_hash":"') - 1
                                          + LENGTH('"ebay_product_images_hash":"') + 40
                                ),
                                SUBSTRING(`additional_data`,
                                          INSTR(`additional_data`, '"ebay_product_images_hash":"')
                                          + LENGTH('"ebay_product_images_hash":"') + 40 + 9
                                )
                            )
    WHERE `additional_data` REGEXP '"ebay_product_images_hash":[^#]+#[0-9]{8}"';

    UPDATE `{$this->_installer->getTable('m2epro_listing_product')}`
    SET `additional_data` = CONCAT(
                                SUBSTRING(`additional_data`,
                                          1,
                                          INSTR(`additional_data`, '"ebay_product_variation_images_hash":"') - 1
                                          + LENGTH('"ebay_product_variation_images_hash":"') + 40
                                ),
                                SUBSTRING(`additional_data`,
                                          INSTR(`additional_data`, '"ebay_product_variation_images_hash":"')
                                          + LENGTH('"ebay_product_variation_images_hash":"') + 40 + 9
                                )
                            )
    WHERE `additional_data` REGEXP '"ebay_product_variation_images_hash":[^#]+#[0-9]{8}"';

SQL
        );

        //########################################

        $accountTable = $installer->getTablesObject()->getFullName('account');
        $ebayAccountTable = $installer->getTablesObject()->getFullName('ebay_account');

        $result = $installer->getConnection()->query("
  SELECT ma.title, mea.info, ma.id
  FROM {$accountTable} ma
  INNER JOIN {$ebayAccountTable} mea ON ma.id = mea.account_id;
")->fetchAll(PDO::FETCH_ASSOC);

        if ($result !== false) {

            foreach ($result as $row) {

                $accountInfo = @json_decode($row['info'], true);

                if (empty($accountInfo['UserID'])) {
                    $userId = trim(preg_replace('/\(\d+\)$/', '', $row['title']));
                } else {
                    $userId = $accountInfo['UserID'];
                }

                $userId = $connection->quote($userId);

                $installer->run(<<<SQL
            UPDATE `{$this->_installer->getTable('m2epro_ebay_account')}`
            SET `user_id` = {$userId}
            WHERE `account_id` = {$row['id']};
SQL
                );
            }
        }

        //########################################

        $result = $connection->query("
SELECT `account_id`,
       `other_listings_mapping_settings`
FROM `{$installer->getTablesObject()->getFullName('buy_account')}`
")->fetchAll(PDO::FETCH_ASSOC);

        if ($result !== false) {

            foreach ($result as $row) {

                $settings = @json_decode($row['other_listings_mapping_settings'], true);

                if (isset($settings['title'])) {

                    unset($settings['title']);
                    $settings = $connection->quote(json_encode($settings));

                    $installer->run(<<<SQL
                UPDATE `{$this->_installer->getTable('m2epro_buy_account')}`
                SET `other_listings_mapping_settings` = {$settings}
                WHERE `account_id` = {$row['account_id']}
SQL
                    );
                }
            }
        }

        //########################################

        if ($installer->getTablesObject()->isExists('locked_object') &&
            $installer->getTablesObject()->isExists('processing_request')) {

            $installer->run(<<<SQL

DELETE `mlo`
FROM `{$this->_installer->getTable('m2epro_locked_object')}` AS `mlo`
LEFT JOIN `{$this->_installer->getTable('m2epro_processing_request')}` AS `mpr` ON `mlo`.`related_hash` = `mpr`.`hash`
WHERE `mpr`.`id` IS NULL;

SQL
            );
        }
    }

    //########################################
}