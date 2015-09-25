<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Upgrade_Migration_ToVersion6
{
    const DEVELOPMENT = false;

    const PREFIX_TABLE_BACKUP = '__backup_v5';

    private $prefixSource = '__source';

    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    private $installer = NULL;

    //####################################

    private $generalDescriptionCorrelation = array();
    private $unusedTemplatesDescriptionIds = array();

    //####################################

    public function getInstaller()
    {
        return $this->installer;
    }

    public function setInstaller(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer)
    {
        $this->installer = $installer;
    }

    //------------------------------------

    public function startSetup()
    {
        $this->installer->startSetup();
    }

    public function endSetup()
    {
        $this->installer->endSetup();
    }

    //####################################

    private function prepareDevelopmentEnvironment()
    {
        $db = (string)Mage::getConfig()->getNode('global/resources/default_setup/connection/dbname');

        $sourceTables = $this->installer->getConnection()->query(
            "SHOW TABLES FROM {$db}
            WHERE
            Tables_in_{$db} LIKE '%{$this->prefixSource}_%'"
        )->fetchAll();

        if (empty($sourceTables)) {

            $tables = $this->installer->getConnection()->query(
                "SHOW TABLES FROM {$db}
                WHERE
                Tables_in_{$db} LIKE 'm2epro_%' OR
                Tables_in_{$db} LIKE 'ess_%'"
            )->fetchAll();

            $sourceTables = array();
            foreach ($tables as $table) {
                $oldTable = current($table);

                $newTable = str_replace('|ess','|ess'.$this->prefixSource,'|'.$oldTable);
                $newTable = str_replace('|m2epro','|m2epro'.$this->prefixSource,$newTable);

                $newTable = ltrim($newTable,'|');

                $sourceTables[] = array('table' => $newTable);
                $this->installer->getConnection()->query("RENAME TABLE `{$oldTable}` TO `{$newTable}`");
            }
        }

        $tablesToDelete = $this->installer->getConnection()->query(
            "SHOW TABLES FROM {$db}
            WHERE
            (Tables_in_{$db} LIKE 'm2epro_%' OR Tables_in_{$db} LIKE 'ess_%')
             AND Tables_in_{$db} NOT LIKE '%{$this->prefixSource}%'"
        )->fetchAll();

        foreach ($tablesToDelete as $table) {
            $table = current($table);
            $this->installer->getConnection()->query("DROP TABLE `{$table}`");
        }

        foreach ($sourceTables as $table) {
            $sourceTable = current($table);

            $workingTable = str_replace('|ess'.$this->prefixSource,'|ess','|'.$sourceTable);
            $workingTable = str_replace('|m2epro'.$this->prefixSource,'|m2epro',$workingTable);

            $workingTable = ltrim($workingTable,'|');

            $this->installer->getConnection()->multi_query(<<<SQL
CREATE TABLE `{$workingTable}` LIKE `{$sourceTable}`;
INSERT INTO `{$workingTable}` SELECT * FROM `{$sourceTable}`
SQL
            );
        }
    }

    //####################################

    public function backup()
    {
        self::DEVELOPMENT && $this->prepareDevelopmentEnvironment();

        if (self::DEVELOPMENT) {
            $startTime = microtime(true);
        }

        !$this->checkToSkipStep('m2epro'.self::PREFIX_TABLE_BACKUP.'_translation_text') &&
            $this->backupOldVersionTables();

        if (self::DEVELOPMENT) {
            echo 'Total Backup Time: '.(string)round(microtime(true) - $startTime,2).'s.';
        }
    }

    public function migrate()
    {
        if (self::DEVELOPMENT) {
            $startTime = microtime(true);
        }

        $this->truncateTables();
        !$this->checkToSkipStep('m2epro_config') &&
            $this->createMigrationTable();

        !$this->checkToSkipStep('m2epro_primary_config') &&
            $this->processConfigTable();
        !$this->checkToSkipStep('m2epro_synchronization_config') &&
            $this->processPrimaryConfigTable();
        !$this->checkToSkipStep('m2epro_cache_config') &&
            $this->processSynchronizationConfigTable();
        !$this->checkToSkipStep('m2epro_wizard') &&
            $this->createCacheConfigTable();

        !$this->checkToSkipStep('m2epro_attribute_set') &&
            $this->processWizardTable();
        !$this->checkToSkipStep('m2epro_listing_category') &&
            $this->processAttributeSetTable();
        !$this->checkToSkipStep('m2epro_stop_queue') &&
            $this->processListingCategoryTable();

        // skip is not necessary
        $this->createStopQueueTable();

        !$this->checkToSkipStep('m2epro_ebay_account_store_category') &&
            $this->processEbayAccountTable();
        !$this->checkToSkipStep('m2epro_amazon_account') &&
            $this->processEbayAccountStoreCategoryTable();
        !$this->checkToSkipStep('m2epro_template_selling_format') &&
            $this->processAmazonAccountTable();

        !$this->checkToSkipStep('m2epro_ebay_template_selling_format') &&
            $this->processTemplateSellingFormatTable();
        !$this->checkToSkipStep('m2epro_amazon_template_selling_format') &&
            $this->processEbayTemplateSellingFormatTable();
        !$this->checkToSkipStep('m2epro_buy_template_selling_format') &&
            $this->processAmazonTemplateSellingFormatTable();
        !$this->checkToSkipStep('m2epro_play_template_selling_format') &&
            $this->processBuyTemplateSellingFormatTable();
        !$this->checkToSkipStep('m2epro_template_synchronization') &&
            $this->processPlayTemplateSellingFormatTable();

        !$this->checkToSkipStep('m2epro_ebay_template_synchronization') &&
            $this->processTemplateSynchronizationTable();
        !$this->checkToSkipStep('m2epro_amazon_template_synchronization') &&
            $this->processEbayTemplateSynchronizationTable();
        !$this->checkToSkipStep('m2epro_buy_template_synchronization') &&
            $this->processAmazonTemplateSynchronizationTable();
        !$this->checkToSkipStep('m2epro_play_template_synchronization') &&
            $this->processBuyTemplateSynchronizationTable();
        !$this->checkToSkipStep('m2epro_ebay_template_return') &&
            $this->processPlayTemplateSynchronizationTable();

        !$this->checkToSkipStep('m2epro_ebay_template_payment') &&
            $this->processEbayTemplateReturnTable();
        !$this->checkToSkipStep('m2epro_ebay_template_payment_service') &&
            $this->processEbayTemplatePaymentTable();
        !$this->checkToSkipStep('m2epro_ebay_template_shipping') &&
            $this->processEbayTemplatePaymentServiceTable();
        !$this->checkToSkipStep('m2epro_ebay_template_shipping_calculated') &&
            $this->processEbayTemplateShippingTable();
        !$this->checkToSkipStep('m2epro_ebay_template_shipping_service') &&
            $this->processEbayTemplateShippingCalculatedTable();
        !$this->checkToSkipStep('m2epro_ebay_template_category') &&
            $this->processEbayTemplateShippingServiceTable();
        !$this->checkToSkipStep('m2epro_ebay_template_category_specific') &&
            $this->processEbayTemplateCategoryTable();
        !$this->checkToSkipStep('m2epro_ebay_template_description') &&
            $this->processEbayTemplateCategorySpecificTable();
        !$this->checkToSkipStep('m2epro_listing') &&
            $this->createEbayTemplateDescriptionTable();

        !$this->checkToSkipStep('m2epro_ebay_listing') &&
            $this->processListingTable();
        !$this->checkToSkipStep('m2epro_amazon_listing') &&
            $this->processEbayListingAndEbayTemplateDescriptionTables();
        !$this->checkToSkipStep('m2epro_buy_listing') &&
            $this->processAmazonListingTable();
        !$this->checkToSkipStep('m2epro_play_listing') &&
            $this->processBuyListingTable();
        !$this->checkToSkipStep('m2epro_listing_product') &&
            $this->processPlayListingTable();

        !$this->checkToSkipStep('m2epro_ebay_listing_product') &&
            $this->createListingProductTable();
        !$this->checkToSkipStep('m2epro_amazon_listing_product') &&
            $this->processEbayListingProductTable();
        !$this->checkToSkipStep('m2epro_buy_listing_product') &&
            $this->processAmazonListingProductTable();
        !$this->checkToSkipStep('m2epro_play_listing_product') &&
            $this->processBuyListingProductTable();
        !$this->checkToSkipStep('m2epro_listing_product_variation') &&
            $this->processPlayListingProductTable();

        !$this->checkToSkipStep('m2epro_ebay_listing_product_variation') &&
            $this->processListingProductVariationTable();
        !$this->checkToSkipStep('m2epro_ebay_marketplace') &&
            $this->processEbayListingProductVariationTable();

        !$this->checkToSkipStep('m2epro_ebay_listing_auto_category') &&
            $this->processEbayMarketplaceTable();
        !$this->checkToSkipStep('m2epro_ebay_template_policy') &&
            $this->processEbayListingAutoCategoryTable();
        !$this->checkToSkipStep('m2epro_ebay_listing_auto_filter') &&
            $this->createEbayTemplatePolicy();
        !$this->checkToSkipStep('m2epro_ebay_dictionary_policy') &&
            $this->createEbayListingAutoFilter();
        !$this->checkToSkipStep('m2epro_ebay_dictionary_policy') &&
            $this->createEbayDictionaryPolicyTable();

        $this->processEbayConditionForMigration();
        $this->processEbayVariationIgnoreForMigration();
        $this->processListingsForMigration();
        $this->processEbayScheduleForMigration();
        $this->processCommonScheduleForMigration();
        $this->processProductDetailsForMigration();
        $this->processEbayUnusedDescriptionTemplatesForMigration();

        if (self::DEVELOPMENT) {
            echo 'Total Migration Time: '.(string)round(microtime(true) - $startTime,2).'s.';
        }
    }

    //####################################

    private function checkToSkipStep($nextTable)
    {
        $nextTable = $this->installer->getTable($nextTable);
        return (bool)$this->installer->tableExists($nextTable);
    }

    private function backupOldVersionTables()
    {
        $oldTables = array(
            'ess_config',
            'm2epro_config',

            'm2epro_attribute_set',

            'm2epro_listing',
            'm2epro_listing_category',
            'm2epro_listing_product',
            'm2epro_listing_product_variation',

            'm2epro_template_description',
            'm2epro_template_general',
            'm2epro_template_selling_format',
            'm2epro_template_synchronization',

            'm2epro_ebay_account_store_category',
            'm2epro_ebay_listing',
            'm2epro_ebay_listing_product',
            'm2epro_ebay_listing_product_variation',
            'm2epro_ebay_marketplace',
            'm2epro_ebay_message',
            'm2epro_ebay_template_description',
            'm2epro_ebay_template_general',
            'm2epro_ebay_template_general_calculated_shipping',
            'm2epro_ebay_template_general_payment',
            'm2epro_ebay_template_general_shipping',
            'm2epro_ebay_template_general_specific',
            'm2epro_ebay_template_selling_format',
            'm2epro_ebay_template_synchronization',

            'm2epro_amazon_account',
            'm2epro_amazon_listing',
            'm2epro_amazon_listing_product',
            'm2epro_amazon_template_general',
            'm2epro_amazon_template_description',
            'm2epro_amazon_template_selling_format',
            'm2epro_amazon_template_synchronization',

            'm2epro_buy_listing',
            'm2epro_buy_listing_product',
            'm2epro_buy_template_general',
            'm2epro_buy_template_description',
            'm2epro_buy_template_selling_format',
            'm2epro_buy_template_synchronization',

            'm2epro_play_listing',
            'm2epro_play_listing_product',
            'm2epro_play_template_general',
            'm2epro_play_template_description',
            'm2epro_play_template_selling_format',
            'm2epro_play_template_synchronization',

            'm2epro_translation_custom_suggestion',
            'm2epro_translation_language',
            'm2epro_translation_text'
        );

        foreach ($oldTables as $oldTable) {

            $newTable = str_replace('|ess','|ess'.self::PREFIX_TABLE_BACKUP,'|'.$oldTable);
            $newTable = str_replace('|m2epro','|m2epro'.self::PREFIX_TABLE_BACKUP,$newTable);

            $newTable = ltrim($newTable,'|');

            $oldTable = $this->installer->getTable($oldTable);
            $newTable = $this->installer->getTable($newTable);

            $this->installer->getConnection()->query("DROP TABLE IF EXISTS `{$newTable}`");
            $this->installer->getConnection()->query("RENAME TABLE `{$oldTable}` TO `{$newTable}`");
        }
    }

    //####################################

    private function truncateTables()
    {
        $tables = array(
            //$this->installer->getTable('m2epro_listing_log'),
            //$this->installer->getTable('m2epro_listing_other_log'),
            //$this->installer->getTable('m2epro_synchronization_log'),
            $this->installer->getTable('m2epro_lock_item'),
            $this->installer->getTable('m2epro_locked_object'),
            $this->installer->getTable('m2epro_processing_request'),
            $this->installer->getTable('m2epro_product_change'),
        );

        $query = '';
        foreach ($tables as $table) {
           $query .= "TRUNCATE {$table}; \n";
        }

        $query && $this->installer->getConnection()->multi_query($query);
    }

    private function createMigrationTable()
    {
        $newTable = $this->installer->getTable('m2epro_migration_v6');

        $this->installer->getConnection()->query(<<<SQL

DROP TABLE IF EXISTS `{$newTable}`;
CREATE TABLE `{$newTable}` (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  component VARCHAR(32) NOT NULL,
  `group` VARCHAR(255) NOT NULL,
  data TEXT DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX `group` (`group`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);
    }

    //####################################

    private function processConfigTable()
    {
        $newTable = $this->installer->getTable('m2epro_config');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_config');

        $this->installer->getConnection()->query(<<<SQL

DROP TABLE IF EXISTS `{$newTable}`;
CREATE TABLE `{$newTable}` (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group` VARCHAR(255) DEFAULT NULL,
  `key` VARCHAR(255) NOT NULL,
  value VARCHAR(255) DEFAULT NULL,
  notice TEXT DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX `group` (`group`),
  INDEX `key` (`key`),
  INDEX value (value)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $oldData = $this->installer->getConnection()->query(<<<SQL
SELECT *
FROM `{$oldTable}`
SQL
)->fetchAll();

        $newData = array();

        // settings, where only group name (or nothing at all) has been changed
        $groupConversion = array(
            '/feedbacks/notification/' => '/view/ebay/feedbacks/notification/', // (mode & last_check)
            '/cron/notification/' => '/view/ebay/cron/notification/', // (mode & max_inactive_hours)
            '/cron/error/' => '/view/common/cron/error/', // (mode & max_inactive_hours)
            '/logs/cleaning/listings/' => '/logs/cleaning/listings/',
            '/logs/cleaning/other_listings/' => '/logs/cleaning/other_listings/',
            '/logs/cleaning/orders/' => '/logs/cleaning/orders/',
            '/logs/cleaning/synchronizations/' => '/logs/cleaning/synchronizations/',
            '/license/validation/domain/notification/' => '/license/validation/domain/notification/',
            '/license/validation/ip/notification/' => '/license/validation/ip/notification/',
            '/license/validation/directory/notification/' => '/license/validation/directory/notification/',
            '/component/ebay/' => '/component/ebay/', // (mode, allowed)
            '/component/amazon/' => '/component/amazon/', // (mode, allowed)
            '/component/buy/' => '/component/buy/', // (mode, allowed)
            '/component/play/' => '/component/play/', // (mode, allowed)
            '/amazon/' => '/amazon/', // (application name)
            '/debug/exceptions/' => '/debug/exceptions/', // (send_to_server)
            '/debug/fatal_error/' => '/debug/fatal_error/', // (send_to_server)
            '/debug/maintenance/' => '/debug/maintenance/', // (mode, restore_date)
            '/templates/description/' => '/renderer/description/', // (convert_linebreaks)
            '/autocomplete/' => '/view/common/autocomplete/', // (max_records_quantity)
            '/cron/task/listings/' => '/cron/task/listings/', // (mode, interval, last_access)
            '/cron/task/other_listings/' => '/cron/task/other_listings/', // (mode, interval, last_access)
            '/cron/task/orders/' => '/cron/task/orders/', // (mode, interval, last_access)
            '/cron/task/synchronization/' => '/cron/task/synchronization/', // (mode, interval, last_access)
            '/cron/task/logs_cleaning/' => '/cron/task/logs_cleaning/', // (mode, interval, last_access)
            '/cron/task/processing/' => '/cron/task/processing/', // (mode, interval, last_access)
            '/cron/task/servicing/' => '/cron/task/servicing/', // (mode, interval, last_access)
            '/cron/' => '/cron/', // (mode, last_access, double_run_protection)
            '/other/paypal/' => '/other/paypal/', // (url)
            '/listings/lockItem/' => '/listings/lockItem/', // (max_deactivate_time)
            '/logs/listings/' => '/logs/listings/', // (last_action_id)
            '/logs/other_listings/' => '/logs/other_listings/', // (last_action_id)
            '/product/index/cataloginventory_stock/' => '/product/index/cataloginventory_stock/', // (disabled)
            '/product/index/' => '/product/index/', // (mode)
            '/order/magento/settings/' => '/order/magento/settings/',
                                                   // (create_with_first_product_options_when_variation_unavailable)
            '/cache/amazon/listing/' => '/view/common/amazon/listing/', // (tutorial_shown)
            '/cache/buy/listing/' => '/view/common/buy/listing/', // (tutorial_shown)
            '/cache/play/listing/' => '/view/common/play/listing/', // (tutorial_shown)
        );

        foreach ($oldData as $oldRow) {

            $newRow = NULL;
            $oldRow['id'] = NULL;

            // notices & thumbnails
            //------------------------------
            if ($oldRow['group'] == '/block_notices/settings/' && $oldRow['key'] == 'show') {
                $newRow = $oldRow;
                $newRow['group'] = '/view/';
                $newRow['key'] = 'show_block_notices';
            }

            if ($oldRow['group'] == '/products/settings/' && $oldRow['key'] == 'show_thumbnails') {
                $newRow = $oldRow;
                $newRow['group'] = '/view/';
                $newRow['key'] = 'show_products_thumbnails';
            }
            //------------------------------

            // default component
            //------------------------------
            if ($oldRow['group'] == '/component/' && $oldRow['key'] == 'default') {
                if ($oldRow['value'] == 'ebay') {

                    $tempData = $this->installer->getConnection()->fetchPairs(<<<SQL
SELECT REPLACE(REPLACE(`group`,'/',''),'component','') ,`value`
FROM `{$oldTable}` WHERE `group` LIKE '/component/%' AND `key` = 'mode'
SQL
);
                    unset($tempData['ebay']);

                    // all components are disabled
                    if (!max($tempData)) {
                        $oldRow['value'] = 'amazon';
                    } elseif ($tempData['amazon']) {
                        $oldRow['value'] = 'amazon';
                    } else {
                        foreach ($tempData as $component => $mode) {
                            $mode && $oldRow['value'] = $component;
                        }
                    }
                }

                $newRow = $oldRow;
                $newRow['group'] = '/view/common/component/';
            }
            //------------------------------

            // /ebay|amazon/order/settings/marketplace_%id%/ (use_first_street_line_as_company)
            //------------------------------
            if (stripos($oldRow['group'], 'order/settings/marketplace_') !== false) {
                $newRow = $oldRow;
            }
            //------------------------------

            //------------------------------
            if (isset($groupConversion[$oldRow['group']])) {
                $newRow = $oldRow;
                $newRow['group'] = $groupConversion[$oldRow['group']];
            }
            //------------------------------

            if (!is_null($newRow)) {
                $newData[] = $newRow;
            }
        }

        $this->installer->getConnection()->query("TRUNCATE {$newTable}");
        !empty($newData) && $this->installer->getConnection()->insertMultiple($newTable, $newData);

        // settings, that could be missing
        $missingSettings = array(
            '/debug/maintenance/' => array(
                'group' => '/debug/maintenance/',
                'keys' => array(
                    array('key' => 'mode', 'default' => 0),
                    array('key' => 'restore_date', 'default' => NULL),
                )
            ),
            '/templates/description/' => array(
                'group' => '/renderer/description/',
                'keys' => array(
                    array('key' => 'convert_linebreaks', 'default' => 1),
                )
            ),
        );

        $newData = array();

        foreach ($missingSettings as $oldGroup => $data) {

            foreach ($data['keys'] as $keyData) {

                $found = false;

                foreach ($oldData as $oldRow) {

                    if ($oldRow['group'] != $oldGroup ||
                        $oldRow['key']   != $keyData['key']) {
                        continue;
                    }

                    $newRow = array(
                        'id' => NULL,
                        'group' => $data['group'],
                        'key'   => $oldRow['key'],
                        'value' => $oldRow['value']
                    );

                    $newData[] = $newRow;

                    $found = true;

                    break;
                }

                if (!$found) {
                    $newData[] = array('id'    => NULL,
                                       'group' => $data['group'],
                                       'key'   => $keyData['key'],
                                       'value' => $keyData['default']);
                }
            }

        }

        !empty($newData) && $this->installer->getConnection()->insertMultiple($newTable, $newData);

        $tempMagentoConnectUrl = 'http://www.magentocommerce.com/magento-connect/customer-experience/';
        $tempMagentoConnectUrl .= 'alternative-sales-models/ebay-magento-integration-order-importing-and-stock-level';
        $tempMagentoConnectUrl .= '-synchronization-9193.html';

        $this->installer->getConnection()->query(<<<SQL
INSERT INTO `{$newTable}` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/view/ebay/', 'mode', 'advanced', 'simple, advanced', '', ''),
('/view/ebay/notice/', 'disable_collapse', '0', '0 - disable, 1 - enable', '', ''),
('/view/ebay/cron/popup/', 'confirm', '1', '0 - disable, 1 - enable', '', ''),
('/view/ebay/template/category/', 'show_tax_category', 0, '', '', ''),
('/view/ebay/advanced/autoaction_popup/', 'shown', '0', '', '', ''),
('/support/', 'documentation_url', 'http://docs.m2epro.com/display/eBayAmazonRakutenPlayMagentoV52/', '', '', ''),
('/support/', 'video_tutorials_url',
 'http://docs.m2epro.com/display/eBayAmazonRakutenPlayMagentoV52/Video+Tutorials', '', '', ''),
('/support/', 'knowledge_base_url', 'http://support.m2epro.com/knowledgebase', '', '', ''),
('/support/', 'clients_portal_url', 'https://m2epro.com/clients/', '', '', ''),
('/support/', 'main_website_url', 'http://m2epro.com/', '', '', ''),
('/support/', 'main_support_url', 'http://support.m2epro.com/', '', '', ''),
('/support/', 'magento_connect_url', '{$tempMagentoConnectUrl}', '', '', ''),
('/support/', 'contact_email', 'support@m2epro.com', '', '', ''),
('/support/uservoice/', 'api_url', 'http://magento2ebay.uservoice.com/api/v1/', '', '', ''),
('/support/uservoice/', 'api_client_key', 'WEsfO8nFh3FXffUU1Oa7A', '', '', '');
SQL
);
    }

    private function processPrimaryConfigTable()
    {
        $newTable = $this->installer->getTable('m2epro_primary_config');
        $oldTable = $this->installer->getTable('ess'.self::PREFIX_TABLE_BACKUP.'_config');

        $this->installer->getConnection()->query(<<<SQL

DROP TABLE IF EXISTS `{$newTable}`;
CREATE TABLE `{$newTable}` (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group` VARCHAR(255) DEFAULT NULL,
  `key` VARCHAR(255) NOT NULL,
  value VARCHAR(255) DEFAULT NULL,
  notice TEXT DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX `group` (`group`),
  INDEX `key` (`key`),
  INDEX value (value)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

INSERT `{$newTable}` SELECT * FROM `{$oldTable}`;

SQL
);
    }

    private function processSynchronizationConfigTable()
    {
        $newTable = $this->installer->getTable('m2epro_synchronization_config');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_config');

        $this->installer->getConnection()->query(<<<SQL

DROP TABLE IF EXISTS `{$newTable}`;
CREATE TABLE `{$newTable}` (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group` VARCHAR(255) DEFAULT NULL,
  `key` VARCHAR(255) NOT NULL,
  value VARCHAR(255) DEFAULT NULL,
  notice TEXT DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX `group` (`group`),
  INDEX `key` (`key`),
  INDEX value (value)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $data = $this->installer->getConnection()->query(<<<SQL
SELECT *
FROM `{$oldTable}`
WHERE `group` LIKE '/synchronization/%'
OR    `group` LIKE '/ebay/synchronization/%'
OR    `group` LIKE '/amazon/synchronization/%'
OR    `group` LIKE '/buy/synchronization/%'
OR    `group` LIKE '/play/synchronization/%'

SQL
)->fetchAll();

        $ignoreGroups = array(
            array('group' => '/templates/end/', 'key' => '*'),
            array('group' => '/templates/start/', 'key' => '*'),
            array('group' => '/messages/', 'key' => '*'),
            array('group' => '/other_listing/source/', 'key' => 'customer_group_id'),
        );

        foreach ($data as $key => &$item) {

            $item['id'] = NULL;

            foreach ($ignoreGroups as $groupData) {

                if (stripos($item['group'],$groupData['group']) === false) {
                    continue;
                }

                if ($groupData['key'] != '*' && $groupData['key'] != $item['key']) {
                    continue;
                }

                unset($data[$key]);
                continue 2;
            }

            $item['group'] = str_replace('/synchronization/settings', NULL, $item['group']);
            $item['group'] = str_replace('/synchronization/', '/settings/', $item['group']);

            $item['group'] == '/' && $item['group'] = NULL;
        }

        !empty($data) && $this->installer->getConnection()->insertMultiple($newTable, $data);

        $this->installer->getConnection()->query(<<<SQL

UPDATE {$newTable}
SET `group` = CONCAT(`group`,'circle/')
WHERE `group` = '/defaults/inspector/';

INSERT INTO {$newTable} (`group`, `key`, value, notice, update_date, create_date)
VALUES
    ('/defaults/inspector/',  'mode', 'circle', '', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
    ('/defaults/stop_queue/', 'mode', '1', '0 - disable, 1 - enable', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
    ('/defaults/stop_queue/', 'interval', '3600', 'in seconds', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
    ('/defaults/stop_queue/', 'last_time', NULL, 'Last check time', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
    ('/ebay/defaults/remove_unused_templates/', 'mode', '1', '0 - disable, 1 - enable', '2012-05-21 10:47:49',
     '2012-05-21 10:47:49'),
    ('/ebay/defaults/remove_unused_templates/', 'interval', '86400', 'in seconds', '2012-05-21 10:47:49',
     '2012-05-21 10:47:49'),
    ('/ebay/defaults/remove_unused_templates/', 'last_time', NULL, 'Last check time', '2012-05-21 10:47:49',
     '2012-05-21 10:47:49'),
    ('/settings/product_change/', 'max_count_per_one_time', '500', NULL, '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
    ('/settings/product_change/', 'max_lifetime', '86400', 'in seconds', '2012-05-21 10:47:49', '2012-05-21 10:47:49'),
    ('/settings/product_change/', 'max_count', '10000', NULL, '2012-05-21 10:47:49', '2012-05-21 10:47:49');
SQL
        );
    }

    //------------------------------------

    private function createCacheConfigTable()
    {
        $newTable = $this->installer->getTable('m2epro_cache_config');

        $this->installer->getConnection()->query(<<<SQL

DROP TABLE IF EXISTS `{$newTable}`;
CREATE TABLE `{$newTable}` (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group` VARCHAR(255) DEFAULT NULL,
  `key` VARCHAR(255) NOT NULL,
  value VARCHAR(255) DEFAULT NULL,
  notice TEXT DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX `group` (`group`),
  INDEX `key` (`key`),
  INDEX value (value)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);
    }

    //####################################

    private function processWizardTable()
    {
        $newWizardTable = $this->installer->getTable('m2epro_wizard');
        $configTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_config');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newWizardTable};
CREATE TABLE {$newWizardTable} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `nick` varchar(255) NOT NULL,
  `view` varchar(255) NOT NULL,
  `status` int(11) unsigned NOT NULL,
  `step` varchar(255) DEFAULT NULL,
  `type` TINYINT(2) UNSIGNED NOT NULL,
  `priority` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `nick` (`nick`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        //----------------------------------------

        $this->installer->getConnection()->insert($newWizardTable,array(
            'nick' => 'migrationToV6',
            'view' => '*',
            'step' => NULL,
            'status' => 0,
            'type' => 1,
            'priority' => 1
        ));

        //----------------------------------------

        $step = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/main/' AND `key` = 'step'")
        );
        $status = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/main/' AND `key` = 'status'")
        );

        $this->installer->getConnection()->insert($newWizardTable,array(
            'nick' => 'installationCommon',
            'view' => 'common',
            'step' => $step,
            'status' => $status,
            'type' => 1,
            'priority' => 2
        ));

        //----------------------------------------

        $status = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/ebay/' AND `key` = 'status'")
        );

        $this->installer->getConnection()->insert($newWizardTable,array(
            'nick' => 'installationEbay',
            'view' => 'ebay',
            'step' => NULL,
            'status' => $status,
            'type' => 1,
            'priority' => 2
        ));

        //----------------------------------------

        $step = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/amazon/' AND `key` = 'step'")
        );
        $status = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/amazon/' AND `key` = 'status'")
        );

        $this->installer->getConnection()->insert($newWizardTable,array(
            'nick' => 'amazon',
            'view' => 'common',
            'step' => $step,
            'status' => $status,
            'type' => 0,
            'priority' => 3
        ));

        //----------------------------------------

        $step = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/buy/' AND `key` = 'step'")
        );
        $status = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/buy/' AND `key` = 'status'")
        );

        $this->installer->getConnection()->insert($newWizardTable,array(
            'nick' => 'buy',
            'view' => 'common',
            'step' => $step,
            'status' => $status,
            'type' => 0,
            'priority' => 4
        ));

        //----------------------------------------

        $step = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/amazonNewAsin/' AND `key` = 'step'")
        );
        $status = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/amazonNewAsin/' AND `key` = 'status'")
        );

        $this->installer->getConnection()->insert($newWizardTable,array(
            'nick' => 'amazonNewAsin',
            'view' => 'common',
            'step' => $step,
            'status' => $status,
            'type' => 0,
            'priority' => 5
        ));

        //----------------------------------------

        $step = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/buyNewSku/' AND `key` = 'step'")
        );
        $status = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/buyNewSku/' AND `key` = 'status'")
        );

        $this->installer->getConnection()->insert($newWizardTable,array(
            'nick' => 'buyNewSku',
            'view' => 'common',
            'step' => $step,
            'status' => $status,
            'type' => 0,
            'priority' => 6
        ));

        //----------------------------------------

        $step = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/play/' AND `key` = 'step'")
        );
        $status = $this->installer->getConnection()->fetchOne(
            $this->installer->getConnection()
                 ->select()
                 ->from($configTable,'value')
                 ->where("`group` = '/wizard/play/' AND `key` = 'status'")
        );

        $this->installer->getConnection()->insert($newWizardTable,array(
            'nick' => 'play',
            'view' => 'common',
            'step' => $step,
            'status' => $status,
            'type' => 0,
            'priority' => 7
        ));

        //----------------------------------------
    }

    private function processAttributeSetTable()
    {
        $newTable = $this->installer->getTable('m2epro_attribute_set');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_attribute_set');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  object_id INT(11) UNSIGNED NOT NULL,
  object_type TINYINT(2) UNSIGNED NOT NULL,
  attribute_set_id INT(11) UNSIGNED NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX attribute_set_id (attribute_set_id),
  INDEX object_id (object_id),
  INDEX object_type (object_type)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT * FROM `{$oldTable}`
WHERE object_type NOT IN (2,4);

SQL
);
        $listingTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listing');
        $templateSellingFormatTable =
            $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_template_selling_format');

        $this->installer->getConnection()->multi_query(<<<SQL

DELETE {$newTable} FROM {$newTable}
INNER JOIN {$listingTable}
ON {$listingTable}.id = {$newTable}.object_id
WHERE {$newTable}.object_type = 1
AND {$listingTable}.component_mode = 'ebay';

DELETE {$newTable} FROM {$newTable}
INNER JOIN {$templateSellingFormatTable}
ON {$templateSellingFormatTable}.id = {$newTable}.object_id
WHERE {$newTable}.object_type = 3
AND {$templateSellingFormatTable}.component_mode = 'ebay'

SQL
);
    }

    private function processListingCategoryTable()
    {
        $newTable = $this->installer->getTable('m2epro_listing_category');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listing_category');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  listing_id INT(11) UNSIGNED NOT NULL,
  category_id INT(11) UNSIGNED NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX category_id (category_id),
  INDEX listing_id (listing_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $listingTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listing');

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT {$oldTable}.* FROM `{$oldTable}`
INNER JOIN {$listingTable}
ON {$listingTable}.id = {$oldTable}.listing_id
WHERE {$listingTable}.component_mode != 'ebay'

SQL
);
    }

    //------------------------------------

    private function createStopQueueTable()
    {
        $newTable = $this->installer->getTable('m2epro_stop_queue');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  item_data TEXT NOT NULL,
  account_hash VARCHAR(255) NOT NULL,
  marketplace_id INT(11) UNSIGNED DEFAULT NULL,
  component_mode VARCHAR(255) NOT NULL,
  is_processed TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX account_hash (account_hash),
  INDEX component_mode (component_mode),
  INDEX is_processed (is_processed),
  INDEX marketplace_id (marketplace_id)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);
    }

    //####################################

    private function processEbayAccountTable()
    {
        $minSameTitleIndex = 2;
        $maxSameTitleIndex = 10;

        $mainTable = $this->installer->getTable('m2epro_account');
        $ebayTable = $this->installer->getTable('m2epro_ebay_account');

        $select = $this->installer->getConnection()
            ->select()
            ->from($mainTable, array('id', 'title'))
            ->joinRight($ebayTable, 'id = account_id', 'ebay_info');

        $oldData = $this->installer->getConnection()->fetchAll($select);

        if (empty($oldData)) {
            return;
        }

        $titles = array();
        foreach ($oldData as $row) {
            $title = (string)$row['title'];
            $titles[$title] = $minSameTitleIndex;
        }

        $migrationData = array();
        foreach ($oldData as $accountRow) {

            if (empty($accountRow['ebay_info'])) {
                continue;
            }

            $ebayInfo = json_decode($accountRow['ebay_info'], true);
            if (empty($ebayInfo['UserID'])) {
                continue;
            }

            $tempTitle = (string)$ebayInfo['UserID'];
            if (($tempTitle == $accountRow['title']) ||
                (isset($titles[$tempTitle]) && ($titles[$tempTitle] >= $maxSameTitleIndex))
            ) {
                continue;
            }

            if (!isset($titles[$tempTitle])) {
                $titles[$tempTitle] = $minSameTitleIndex;
                $newTitle = $tempTitle;
            } else {
                $newTitle = $tempTitle.' ('.$titles[$tempTitle].')';
                $titles[$tempTitle]++;
            }

            $migrationData[$accountRow['id']] = array(
                'title' => $newTitle,
                'old_title' => $accountRow['title'],
            );

            $this->installer->getConnection()->update(
                $mainTable,
                array('title' => $newTitle),
                array('id = ?' => $accountRow['id'])
            );
        }

        if (empty($migrationData)) {
            return;
        }

        $migrationTable = $this->installer->getTable('m2epro_migration_v6');
        $migrationTableData = array(
            'component' => 'ebay',
            'group' => 'accounts_rename',
            'data' => json_encode($migrationData)
        );
        $this->installer->getConnection()->insert($migrationTable, $migrationTableData);
    }

    private function processEbayAccountStoreCategoryTable()
    {
        $newTable = $this->installer->getTable('m2epro_ebay_account_store_category');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_account_store_category');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `account_id` int(11) unsigned NOT NULL,
  `category_id` decimal(20,0) unsigned NOT NULL,
  `parent_id` decimal(20,0) unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `is_leaf` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `sorder` int(11) unsigned NOT NULL,
  PRIMARY KEY (`account_id`,`category_id`),
  KEY `parent_id` (`parent_id`),
  KEY `sorder` (`sorder`),
  KEY `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $select = $this->installer->getConnection()->select()->from($oldTable, '*');
        $oldData = $this->installer->getConnection()->fetchAll($select);

        if (empty($oldData)) {
            return;
        }

        $parents = array();
        foreach ($oldData as $row) {
            $accountId = (int)$row['account_id'];
            $parentId = (string)$row['parent_id'];
            $parents[$accountId][$parentId] = '';
        }

        $newData = array();
        foreach ($oldData as $row) {

            $accountId = (int)$row['account_id'];
            $categoryId = (string)$row['category_id'];

            $isLeaf = 1;

            if (isset($parents[$accountId][$categoryId])) {
                $isLeaf = 0;
            }

            $row['is_leaf'] = $isLeaf;
            $newData[] = $row;
        }

        !empty($newData) && $this->installer->getConnection()->insertMultiple($newTable, $newData);
    }

    //------------------------------------

    private function processAmazonAccountTable()
    {
        $newTable = $this->installer->getTable('m2epro_amazon_account');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_amazon_account');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `account_id` int(11) unsigned NOT NULL,
  `server_hash` varchar(255) NOT NULL,
  `marketplace_id` int(11) unsigned NOT NULL,
  `merchant_id` varchar(255) NOT NULL,
  `related_store_id` int(11) NOT NULL DEFAULT '0',
  `other_listings_synchronization` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `other_listings_mapping_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `other_listings_mapping_settings` varchar(255) DEFAULT NULL,
  `other_listings_move_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `other_listings_move_settings` varchar(255) DEFAULT NULL,
  `orders_mode` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `orders_last_synchronization` datetime DEFAULT NULL,
  `magento_orders_settings` text NOT NULL,
  `info` text,
  PRIMARY KEY (`account_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);
        $select = $this->installer->getConnection()->select()->from($oldTable,'*');

        $newRows = array();
        foreach ($this->installer->getConnection()->fetchAll($select) as $row) {

            $row['marketplaces_data'] = json_decode($row['marketplaces_data'], true);
            $row['marketplace_id'] = key($row['marketplaces_data']);
            $row['server_hash'] = $row['marketplaces_data'][$row['marketplace_id']]['server_hash'];
            $row['merchant_id'] = $row['marketplaces_data'][$row['marketplace_id']]['merchant_id'];
            $row['related_store_id'] = $row['marketplaces_data'][$row['marketplace_id']]['related_store_id'];
            $row['info'] = json_encode($row['marketplaces_data'][$row['marketplace_id']]['info']);
            unset($row['marketplaces_data']);

            $newRows[] = $row;
        }

        !empty($newRows) && $this->installer->getConnection()->insertMultiple($newTable, $newRows);
    }

    //####################################

    private function processTemplateSynchronizationTable()
    {
        $newTable = $this->installer->getTable('m2epro_template_synchronization');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_template_synchronization');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `revise_change_listing` tinyint(2) unsigned NOT NULL,
  `revise_change_selling_format_template` tinyint(2) unsigned NOT NULL,
  `component_mode` varchar(10) DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `component_mode` (`component_mode`),
  KEY `revise_change_listing` (`revise_change_listing`),
  KEY `revise_change_selling_format_template` (`revise_change_selling_format_template`),
  KEY `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `id`,
       `title`,
       IF(`component_mode` = 'ebay', 0, `revise_change_general_template`),
       `revise_change_selling_format_template`,
       `component_mode`,
       `update_date`,
       `create_date`
FROM `{$oldTable}`;

SQL
);
    }

    //------------------------------------

    private function processEbayTemplateSynchronizationTable()
    {
        $newTable = $this->installer->getTable('m2epro_ebay_template_synchronization');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  template_synchronization_id INT(11) UNSIGNED NOT NULL,
  is_custom_template TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  list_mode TINYINT(2) UNSIGNED NOT NULL,
  list_status_enabled TINYINT(2) UNSIGNED NOT NULL,
  list_is_in_stock TINYINT(2) UNSIGNED NOT NULL,
  list_qty TINYINT(2) UNSIGNED NOT NULL,
  list_qty_value INT(11) UNSIGNED NOT NULL,
  list_qty_value_max INT(11) UNSIGNED NOT NULL,
  revise_update_qty TINYINT(2) UNSIGNED NOT NULL,
  revise_update_qty_max_applied_value_mode TINYINT(2) UNSIGNED NOT NULL,
  revise_update_qty_max_applied_value INT(11) UNSIGNED DEFAULT NULL,
  revise_update_price TINYINT(2) UNSIGNED NOT NULL,
  revise_update_title TINYINT(2) UNSIGNED NOT NULL,
  revise_update_sub_title TINYINT(2) UNSIGNED NOT NULL,
  revise_update_description TINYINT(2) UNSIGNED NOT NULL,
  revise_change_category_template TINYINT(2) UNSIGNED NOT NULL,
  revise_change_payment_template TINYINT(2) UNSIGNED NOT NULL,
  revise_change_return_template TINYINT(2) UNSIGNED NOT NULL,
  revise_change_shipping_template TINYINT(2) UNSIGNED NOT NULL,
  revise_change_description_template TINYINT(2) UNSIGNED NOT NULL,
  relist_mode TINYINT(2) UNSIGNED NOT NULL,
  relist_filter_user_lock TINYINT(2) UNSIGNED NOT NULL,
  relist_send_data TINYINT(2) UNSIGNED NOT NULL,
  relist_status_enabled TINYINT(2) UNSIGNED NOT NULL,
  relist_is_in_stock TINYINT(2) UNSIGNED NOT NULL,
  relist_qty TINYINT(2) UNSIGNED NOT NULL,
  relist_qty_value INT(11) UNSIGNED NOT NULL,
  relist_qty_value_max INT(11) UNSIGNED NOT NULL,
  stop_status_disabled TINYINT(2) UNSIGNED NOT NULL,
  stop_out_off_stock TINYINT(2) UNSIGNED NOT NULL,
  stop_qty TINYINT(2) UNSIGNED NOT NULL,
  stop_qty_value INT(11) UNSIGNED NOT NULL,
  stop_qty_value_max INT(11) UNSIGNED NOT NULL,
  schedule_mode TINYINT(2) UNSIGNED NOT NULL,
  schedule_interval_settings TEXT NULL DEFAULT NULL,
  schedule_week_settings TEXT NULL DEFAULT NULL,
  PRIMARY KEY (template_synchronization_id),
  INDEX is_custom_template (is_custom_template)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $templateSynchTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_template_synchronization'
        );
        $ebayTemplateSynchTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_template_synchronization'
        );

        $oldRows = $this->installer->getConnection()->query(<<<SQL
SELECT `{$ebayTemplateSynchTable}`.`template_synchronization_id`,
       0 AS `is_custom_template`,
       `{$ebayTemplateSynchTable}`.`list_mode`,
       `{$ebayTemplateSynchTable}`.`list_status_enabled`,
       `{$ebayTemplateSynchTable}`.`list_is_in_stock`,
       `{$ebayTemplateSynchTable}`.`list_qty`,
       `{$ebayTemplateSynchTable}`.`list_qty_value`,
       `{$ebayTemplateSynchTable}`.`list_qty_value_max`,
       `{$ebayTemplateSynchTable}`.`revise_update_qty`,
       IF(`{$ebayTemplateSynchTable}`.`revise_update_qty_max_applied_value` IS NULL,0,1)
        AS revise_update_qty_max_applied_value_mode,
       `{$ebayTemplateSynchTable}`.`revise_update_qty_max_applied_value`,
       `{$ebayTemplateSynchTable}`.`revise_update_price`,
       `{$ebayTemplateSynchTable}`.`revise_update_title`,
       `{$ebayTemplateSynchTable}`.`revise_update_sub_title`,
       `{$ebayTemplateSynchTable}`.`revise_update_description`,
       `{$templateSynchTable}`.`revise_change_general_template` AS `revise_change_category_template`,
       `{$templateSynchTable}`.`revise_change_general_template` AS `revise_change_payment_template`,
       `{$templateSynchTable}`.`revise_change_general_template` AS `revise_change_return_template`,
       `{$templateSynchTable}`.`revise_change_general_template` AS `revise_change_shipping_template`,
       `{$templateSynchTable}`.`revise_change_description_template`,
       `{$ebayTemplateSynchTable}`.`relist_mode`,
       `{$ebayTemplateSynchTable}`.`relist_filter_user_lock`,
       `{$ebayTemplateSynchTable}`.`relist_send_data`,
       `{$ebayTemplateSynchTable}`.`relist_status_enabled`,
       `{$ebayTemplateSynchTable}`.`relist_is_in_stock`,
       `{$ebayTemplateSynchTable}`.`relist_qty`,
       `{$ebayTemplateSynchTable}`.`relist_qty_value`,
       `{$ebayTemplateSynchTable}`.`relist_qty_value_max`,
       `{$ebayTemplateSynchTable}`.`stop_status_disabled`,
       `{$ebayTemplateSynchTable}`.`stop_out_off_stock`,
       `{$ebayTemplateSynchTable}`.`stop_qty`,
       `{$ebayTemplateSynchTable}`.`stop_qty_value`,
       `{$ebayTemplateSynchTable}`.`stop_qty_value_max`,

       `{$ebayTemplateSynchTable}`.`relist_schedule_type`,
       `{$ebayTemplateSynchTable}`.`relist_schedule_week`,
       `{$ebayTemplateSynchTable}`.`relist_schedule_week_start_time`,
       `{$ebayTemplateSynchTable}`.`relist_schedule_week_end_time`

FROM `{$ebayTemplateSynchTable}`
INNER JOIN `{$templateSynchTable}`
  ON `{$templateSynchTable}`.`id` = `{$ebayTemplateSynchTable}`.`template_synchronization_id`;
SQL
)->fetchAll();

        $newRows = array();
        foreach ($oldRows as $oldRow) {
            $newRow = $oldRow;
            unset(
                $newRow['relist_schedule_type'],
                $newRow['relist_schedule_week'],
                $newRow['relist_schedule_week_start_time'],
                $newRow['relist_schedule_week_end_time']
            );

            $newRow['schedule_mode'] = 0;
            $newRow['schedule_interval_settings'] = NULL;
            $newRow['schedule_week_settings'] = NULL;

            if ($oldRow['relist_schedule_type'] == 2) {
                $newRow['schedule_mode'] = 1;
                $newRow['schedule_interval_settings'] = json_encode(array(
                    'mode' => 0,
                    'date_from' => NULL,
                    'date_to'   => NULL,
                ));

                $newRow['schedule_week_settings'] = array();

                if (empty($oldRow['relist_schedule_week_start_time']) ||
                    empty($oldRow['relist_schedule_week_end_time'])) {
                    $timeFrom = '00:01:00';
                    $timeTo   = '23:59:00';
                } else {
                    $timeFrom = $oldRow['relist_schedule_week_start_time'];
                    $timeTo   = $oldRow['relist_schedule_week_end_time'];
                }

                $daysOfWeeks = array(
                    'monday','tuesday','wednesday','thursday','friday','saturday','sunday'
                );

                list($monday,$tuesday,$wednesday,$thursday,$friday,$saturday,$sunday) = explode(
                    '_',$oldRow['relist_schedule_week']
                );

                foreach ($daysOfWeeks as $day) {
                    $daySettings = $$day;
                    if ($daySettings{2}) {
                        $newRow['schedule_week_settings'][$day] = array(
                            'time_from' => $timeFrom,
                            'time_to' => $timeTo
                        );
                    }
                }

                $newRow['schedule_week_settings'] = json_encode($newRow['schedule_week_settings']);
            }

            $newRows[] = $newRow;
        }

        !empty($newRows) && $this->installer->getConnection()->insertMultiple($newTable,$newRows);
    }

    private function processAmazonTemplateSynchronizationTable()
    {
        $newTable = $this->installer->getTable('m2epro_amazon_template_synchronization');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_amazon_template_synchronization');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `template_synchronization_id` int(11) unsigned NOT NULL,
  `list_mode` tinyint(2) unsigned NOT NULL,
  `list_status_enabled` tinyint(2) unsigned NOT NULL,
  `list_is_in_stock` tinyint(2) unsigned NOT NULL,
  `list_qty` tinyint(2) unsigned NOT NULL,
  `list_qty_value` int(11) unsigned NOT NULL,
  `list_qty_value_max` int(11) unsigned NOT NULL,
  `revise_update_qty` tinyint(2) unsigned NOT NULL,
  `revise_update_qty_max_applied_value_mode` tinyint(2) unsigned NOT NULL,
  `revise_update_qty_max_applied_value` int(11) unsigned DEFAULT NULL,
  `revise_update_price` tinyint(2) unsigned NOT NULL,
  `relist_mode` tinyint(2) unsigned NOT NULL,
  `relist_filter_user_lock` tinyint(2) unsigned NOT NULL,
  `relist_status_enabled` tinyint(2) unsigned NOT NULL,
  `relist_is_in_stock` tinyint(2) unsigned NOT NULL,
  `relist_qty` tinyint(2) unsigned NOT NULL,
  `relist_qty_value` int(11) unsigned NOT NULL,
  `relist_qty_value_max` int(11) unsigned NOT NULL,
  `stop_status_disabled` tinyint(2) unsigned NOT NULL,
  `stop_out_off_stock` tinyint(2) unsigned NOT NULL,
  `stop_qty` tinyint(2) unsigned NOT NULL,
  `stop_qty_value` int(11) unsigned NOT NULL,
  `stop_qty_value_max` int(11) unsigned NOT NULL,
  PRIMARY KEY (`template_synchronization_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `template_synchronization_id`,
       `list_mode`,
       `list_status_enabled`,
       `list_is_in_stock`,
       `list_qty`,
       `list_qty_value`,
       `list_qty_value_max`,
       `revise_update_qty`,
       IF(`revise_update_qty_max_applied_value` IS NULL,0,1),
       `revise_update_qty_max_applied_value`,
       `revise_update_price`,
       `relist_mode`,
       `relist_filter_user_lock`,
       `relist_status_enabled`,
       `relist_is_in_stock`,
       `relist_qty`,
       `relist_qty_value`,
       `relist_qty_value_max`,
       `stop_status_disabled`,
       `stop_out_off_stock`,
       `stop_qty`,
       `stop_qty_value`,
       `stop_qty_value_max`
FROM `{$oldTable}`;

SQL
);
    }

    private function processBuyTemplateSynchronizationTable()
    {
        $newTable = $this->installer->getTable('m2epro_buy_template_synchronization');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_buy_template_synchronization');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `template_synchronization_id` int(11) unsigned NOT NULL,
  `list_mode` tinyint(2) unsigned NOT NULL,
  `list_status_enabled` tinyint(2) unsigned NOT NULL,
  `list_is_in_stock` tinyint(2) unsigned NOT NULL,
  `list_qty` tinyint(2) unsigned NOT NULL,
  `list_qty_value` int(11) unsigned NOT NULL,
  `list_qty_value_max` int(11) unsigned NOT NULL,
  `revise_update_qty` tinyint(2) unsigned NOT NULL,
  `revise_update_qty_max_applied_value_mode` tinyint(2) unsigned NOT NULL,
  `revise_update_qty_max_applied_value` int(11) unsigned DEFAULT NULL,
  `revise_update_price` tinyint(2) unsigned NOT NULL,
  `relist_mode` tinyint(2) unsigned NOT NULL,
  `relist_filter_user_lock` tinyint(2) unsigned NOT NULL,
  `relist_status_enabled` tinyint(2) unsigned NOT NULL,
  `relist_is_in_stock` tinyint(2) unsigned NOT NULL,
  `relist_qty` tinyint(2) unsigned NOT NULL,
  `relist_qty_value` int(11) unsigned NOT NULL,
  `relist_qty_value_max` int(11) unsigned NOT NULL,
  `stop_status_disabled` tinyint(2) unsigned NOT NULL,
  `stop_out_off_stock` tinyint(2) unsigned NOT NULL,
  `stop_qty` tinyint(2) unsigned NOT NULL,
  `stop_qty_value` int(11) unsigned NOT NULL,
  `stop_qty_value_max` int(11) unsigned NOT NULL,
  PRIMARY KEY (`template_synchronization_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `template_synchronization_id`,
       `list_mode`,
       `list_status_enabled`,
       `list_is_in_stock`,
       `list_qty`,
       `list_qty_value`,
       `list_qty_value_max`,
       `revise_update_qty`,
       IF(`revise_update_qty_max_applied_value` IS NULL,0,1),
       `revise_update_qty_max_applied_value`,
       `revise_update_price`,
       `relist_mode`,
       `relist_filter_user_lock`,
       `relist_status_enabled`,
       `relist_is_in_stock`,
       `relist_qty`,
       `relist_qty_value`,
       `relist_qty_value_max`,
       `stop_status_disabled`,
       `stop_out_off_stock`,
       `stop_qty`,
       `stop_qty_value`,
       `stop_qty_value_max`
FROM `{$oldTable}`;

SQL
);
    }

    private function processPlayTemplateSynchronizationTable()
    {
        $newTable = $this->installer->getTable('m2epro_play_template_synchronization');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_play_template_synchronization');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `template_synchronization_id` int(11) unsigned NOT NULL,
  `list_mode` tinyint(2) unsigned NOT NULL,
  `list_status_enabled` tinyint(2) unsigned NOT NULL,
  `list_is_in_stock` tinyint(2) unsigned NOT NULL,
  `list_qty` tinyint(2) unsigned NOT NULL,
  `list_qty_value` int(11) unsigned NOT NULL,
  `list_qty_value_max` int(11) unsigned NOT NULL,
  `revise_update_qty` tinyint(2) unsigned NOT NULL,
  `revise_update_qty_max_applied_value_mode` tinyint(2) unsigned NOT NULL,
  `revise_update_qty_max_applied_value` int(11) unsigned DEFAULT NULL,
  `revise_update_price` tinyint(2) unsigned NOT NULL,
  `relist_mode` tinyint(2) unsigned NOT NULL,
  `relist_filter_user_lock` tinyint(2) unsigned NOT NULL,
  `relist_status_enabled` tinyint(2) unsigned NOT NULL,
  `relist_is_in_stock` tinyint(2) unsigned NOT NULL,
  `relist_qty` tinyint(2) unsigned NOT NULL,
  `relist_qty_value` int(11) unsigned NOT NULL,
  `relist_qty_value_max` int(11) unsigned NOT NULL,
  `stop_status_disabled` tinyint(2) unsigned NOT NULL,
  `stop_out_off_stock` tinyint(2) unsigned NOT NULL,
  `stop_qty` tinyint(2) unsigned NOT NULL,
  `stop_qty_value` int(11) unsigned NOT NULL,
  `stop_qty_value_max` int(11) unsigned NOT NULL,
  PRIMARY KEY (`template_synchronization_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `template_synchronization_id`,
       `list_mode`,
       `list_status_enabled`,
       `list_is_in_stock`,
       `list_qty`,
       `list_qty_value`,
       `list_qty_value_max`,
       `revise_update_qty`,
       IF(`revise_update_qty_max_applied_value` IS NULL,0,1),
       `revise_update_qty_max_applied_value`,
       `revise_update_price`,
       `relist_mode`,
       `relist_filter_user_lock`,
       `relist_status_enabled`,
       `relist_is_in_stock`,
       `relist_qty`,
       `relist_qty_value`,
       `relist_qty_value_max`,
       `stop_status_disabled`,
       `stop_out_off_stock`,
       `stop_qty`,
       `stop_qty_value`,
       `stop_qty_value_max`
FROM `{$oldTable}`;

SQL
);
    }

    //####################################

    private function processTemplateSellingFormatTable()
    {
        $newTable = $this->installer->getTable('m2epro_template_selling_format');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_template_selling_format');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `component_mode` varchar(10) DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `component_mode` (`component_mode`),
  KEY `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `id`,
       `title`,
       `component_mode`,
       `update_date`,
       `create_date`
FROM `{$oldTable}`;

SQL
);
    }

    //------------------------------------

    private function processEbayTemplateSellingFormatTable()
    {
        $newTable = $this->installer->getTable('m2epro_ebay_template_selling_format');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_template_selling_format');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  template_selling_format_id INT(11) UNSIGNED NOT NULL,
  is_custom_template TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  listing_type TINYINT(2) UNSIGNED NOT NULL,
  listing_type_attribute VARCHAR(255) NOT NULL,
  listing_is_private TINYINT(2) UNSIGNED NOT NULL,
  duration_mode TINYINT(4) UNSIGNED NOT NULL,
  duration_attribute VARCHAR(255) NOT NULL,
  qty_mode TINYINT(2) UNSIGNED NOT NULL,
  qty_custom_value INT(11) UNSIGNED NOT NULL,
  qty_custom_attribute VARCHAR(255) NOT NULL,
  qty_max_posted_value_mode TINYINT(2) UNSIGNED NOT NULL,
  qty_max_posted_value INT(11) UNSIGNED DEFAULT NULL,
  price_variation_mode TINYINT(2) UNSIGNED NOT NULL,
  start_price_mode TINYINT(2) UNSIGNED NOT NULL,
  start_price_coefficient VARCHAR(255) NOT NULL,
  start_price_custom_attribute VARCHAR(255) NOT NULL,
  reserve_price_mode TINYINT(2) UNSIGNED NOT NULL,
  reserve_price_coefficient VARCHAR(255) NOT NULL,
  reserve_price_custom_attribute VARCHAR(255) NOT NULL,
  buyitnow_price_mode TINYINT(2) UNSIGNED NOT NULL,
  buyitnow_price_coefficient VARCHAR(255) NOT NULL,
  buyitnow_price_custom_attribute VARCHAR(255) NOT NULL,
  best_offer_mode TINYINT(2) UNSIGNED NOT NULL,
  best_offer_accept_mode TINYINT(2) UNSIGNED NOT NULL,
  best_offer_accept_value VARCHAR(255) NOT NULL,
  best_offer_accept_attribute VARCHAR(255) NOT NULL,
  best_offer_reject_mode TINYINT(2) UNSIGNED NOT NULL,
  best_offer_reject_value VARCHAR(255) NOT NULL,
  best_offer_reject_attribute VARCHAR(255) NOT NULL,
  PRIMARY KEY (template_selling_format_id),
  INDEX is_custom_template (is_custom_template)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $select = $this->installer->getConnection()->select()->from($oldTable, '*');
        $oldData = $this->installer->getConnection()->fetchAll($select);

        if (empty($oldData)) {
            return;
        }

        $newData = array();
        $migrationData = array();
        foreach ($oldData as $row) {
            $row['is_custom_template'] = 0;
            unset($row['currency']);
            unset($row['customer_group_id']);

            $row['qty_max_posted_value_mode'] = 1;
            if (is_null($row['qty_max_posted_value'])) {
                $row['qty_max_posted_value_mode'] = 0;
            }

            $coefficients = array(
                'start_price_coefficient' => $row['start_price_coefficient'],
                'reserve_price_coefficient' => $row['reserve_price_coefficient'],
                'buyitnow_price_coefficient' => $row['buyitnow_price_coefficient']
            );

            $migrationData[$row['template_selling_format_id']] = array();
            foreach ($coefficients as $key => $coefficient) {
                $coefficient = (string)$coefficient;

                $migrationData[$row['template_selling_format_id']][$key] = array(
                    'old_coefficient' => $coefficient,
                    'is_changed' => false
                );

                if (in_array($coefficient, array('', '0', '1', '100%'))) {
                    $row[$key] = '';
                    $migrationData[$row['template_selling_format_id']][$key]['is_changed'] = true;

                    continue;
                }

                if (strpos($coefficient, '+') === 0 ||
                    strpos($coefficient, '-') === 0
                ) {
                    continue;
                }

                if (strpos($coefficient, '%')) {
                    $value = str_replace('%', '', $coefficient);

                    if ((int)$value > 100) {
                        $row[$key] = '+'.(string)($value-100).'%';
                    } elseif ((int)$value < 100) {
                        $row[$key] = '-'.(string)(100-$value).'%';
                    }

                    $migrationData[$row['template_selling_format_id']][$key]['is_changed'] = true;

                    continue;
                }

                if (strpos($coefficient, '.') !== false) {
                    list($integ, $fract) = explode('.', $coefficient);

                    if ((int)$integ == 0 && (int)$fract == 0) {
                        continue;
                    }

                    if ((int)$integ == 0) {
                        if (strlen($fract) == 1) {
                            $fract = $fract.'0';
                        } elseif (strpos($fract, '0') === 0 && strlen($fract) != 2) {
                            $fract = (float)(substr($fract, 0, 1) . '.' . substr($fract, 1));
                        } else {
                            $fract = (float)(substr($fract, 0, 2) . '.' . substr($fract, 2));
                        }

                        $row[$key] = '-'.(string)(100 - $fract).'%';

                        $migrationData[$row['template_selling_format_id']][$key]['is_changed'] = true;

                        continue;
                    }

                    $percentsString = $integ - 1;
                    if ($percentsString <= 0) {
                        $percentsString = '';
                    }

                    if ((int)$fract == 0) {
                        $percentsString .= '00';
                    } elseif (strlen($fract) == 1) {
                        $percentsString = $fract.'0';
                    } else {
                        $percentsString .= (float)(substr($fract, 0, 2) . '.' . substr($fract, 2));
                    }

                    $row[$key] = '+'.$percentsString.'%';

                    $migrationData[$row['template_selling_format_id']][$key]['is_changed'] = true;

                    continue;
                }

                if (strpos($coefficient, '.') === false && strpos($coefficient, '%') === false) {
                    $row[$key] = '+'.(($coefficient-1)*100).'%';
                    $migrationData[$row['template_selling_format_id']][$key]['is_changed'] = true;
                }
            }

            $newData[] = $row;
        }

        !empty($newData) && $this->installer->getConnection()->insertMultiple($newTable, $newData);

        $migrationTable = $this->installer->getTable('m2epro_migration_v6');
        $migrationTableData = array(
            'component' => 'ebay',
            'group' => 'selling_format_currencies',
            'data' => json_encode($migrationData)
        );
        $this->installer->getConnection()->insert($migrationTable, $migrationTableData);
    }

    private function processAmazonTemplateSellingFormatTable()
    {
        $newTable = $this->installer->getTable('m2epro_amazon_template_selling_format');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_amazon_template_selling_format');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `template_selling_format_id` int(11) unsigned NOT NULL,
  `qty_mode` tinyint(2) unsigned NOT NULL,
  `qty_custom_value` int(11) unsigned NOT NULL,
  `qty_custom_attribute` varchar(255) NOT NULL,
  `qty_max_posted_value_mode` tinyint(2) unsigned NOT NULL,
  `qty_max_posted_value` int(11) unsigned DEFAULT NULL,
  `price_mode` tinyint(2) unsigned NOT NULL,
  `price_custom_attribute` varchar(255) NOT NULL,
  `price_coefficient` varchar(255) NOT NULL,
  `sale_price_mode` tinyint(2) unsigned NOT NULL,
  `sale_price_custom_attribute` varchar(255) NOT NULL,
  `sale_price_coefficient` varchar(255) NOT NULL,
  `price_variation_mode` tinyint(2) unsigned NOT NULL,
  `sale_price_start_date_mode` tinyint(2) unsigned NOT NULL,
  `sale_price_start_date_value` datetime NOT NULL,
  `sale_price_start_date_custom_attribute` varchar(255) NOT NULL,
  `sale_price_end_date_mode` tinyint(2) unsigned NOT NULL,
  `sale_price_end_date_value` datetime NOT NULL,
  `sale_price_end_date_custom_attribute` varchar(255) NOT NULL,
  PRIMARY KEY (`template_selling_format_id`),
  KEY `price_variation_mode` (`price_variation_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `template_selling_format_id`,
       `qty_mode`,
       `qty_custom_value`,
       `qty_custom_attribute`,
       IF(`qty_max_posted_value` IS NULL,0,1),
       `qty_max_posted_value`,
       `price_mode`,
       `price_custom_attribute`,
       `price_coefficient`,
       `sale_price_mode`,
       `sale_price_custom_attribute`,
       `sale_price_coefficient`,
       `price_variation_mode`,
       `sale_price_start_date_mode`,
       `sale_price_start_date_value`,
       `sale_price_start_date_custom_attribute`,
       `sale_price_end_date_mode`,
       `sale_price_end_date_value`,
       `sale_price_end_date_custom_attribute`
FROM `{$oldTable}`;

SQL
);

        $select = $this->installer->getConnection()->select()->from($oldTable, '*');
        $data = $this->installer->getConnection()->fetchAll($select);

        if (empty($data)) {
            return;
        }

        $migrationData = array();
        foreach ($data as $row) {
            $migrationData[(int)$row['template_selling_format_id']] = true;
        }

        $migrationTable = $this->installer->getTable('m2epro_migration_v6');
        $migrationTableData = array(
            'component' => 'amazon',
            'group' => 'selling_format_currencies',
            'data' => json_encode($migrationData)
        );
        $this->installer->getConnection()->insert($migrationTable, $migrationTableData);
    }

    private function processBuyTemplateSellingFormatTable()
    {
        $newTable = $this->installer->getTable('m2epro_buy_template_selling_format');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_buy_template_selling_format');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `template_selling_format_id` int(11) unsigned NOT NULL,
  `qty_mode` tinyint(2) unsigned NOT NULL,
  `qty_custom_value` int(11) unsigned NOT NULL,
  `qty_custom_attribute` varchar(255) NOT NULL,
  `qty_max_posted_value_mode` tinyint(2) unsigned NOT NULL,
  `qty_max_posted_value` int(11) unsigned DEFAULT NULL,
  `price_mode` tinyint(2) unsigned NOT NULL,
  `price_custom_attribute` varchar(255) NOT NULL,
  `price_coefficient` varchar(255) NOT NULL,
  `price_variation_mode` tinyint(2) unsigned NOT NULL,
  PRIMARY KEY (`template_selling_format_id`),
  KEY `price_variation_mode` (`price_variation_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `template_selling_format_id`,
       `qty_mode`,
       `qty_custom_value`,
       `qty_custom_attribute`,
       IF(`qty_max_posted_value` IS NULL,0,1),
       `qty_max_posted_value`,
       `price_mode`,
       `price_custom_attribute`,
       `price_coefficient`,
       `price_variation_mode`
FROM `{$oldTable}`;

SQL
);

        $select = $this->installer->getConnection()->select()->from($oldTable, '*');
        $data = $this->installer->getConnection()->fetchAll($select);

        if (empty($data)) {
            return;
        }

        $migrationData = array();
        foreach ($data as $row) {
            $migrationData[(int)$row['template_selling_format_id']] = true;
        }

        $migrationTable = $this->installer->getTable('m2epro_migration_v6');
        $migrationTableData = array(
            'component' => 'buy',
            'group' => 'selling_format_currencies',
            'data' => json_encode($migrationData)
        );
        $this->installer->getConnection()->insert($migrationTable, $migrationTableData);
    }

    private function processPlayTemplateSellingFormatTable()
    {
        $newTable = $this->installer->getTable('m2epro_play_template_selling_format');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_play_template_selling_format');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `template_selling_format_id` int(11) unsigned NOT NULL,
  `qty_mode` tinyint(2) unsigned NOT NULL,
  `qty_custom_value` int(11) unsigned NOT NULL,
  `qty_custom_attribute` varchar(255) NOT NULL,
  `qty_max_posted_value_mode` tinyint(2) unsigned NOT NULL,
  `qty_max_posted_value` int(11) unsigned DEFAULT NULL,
  `price_gbr_mode` tinyint(2) unsigned NOT NULL,
  `price_gbr_custom_attribute` varchar(255) NOT NULL,
  `price_gbr_coefficient` varchar(255) NOT NULL,
  `price_euro_mode` tinyint(2) unsigned NOT NULL,
  `price_euro_custom_attribute` varchar(255) NOT NULL,
  `price_euro_coefficient` varchar(255) NOT NULL,
  `price_variation_mode` tinyint(2) unsigned NOT NULL,
  PRIMARY KEY (`template_selling_format_id`),
  KEY `price_variation_mode` (`price_variation_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `template_selling_format_id`,
       `qty_mode`,
       `qty_custom_value`,
       `qty_custom_attribute`,
       IF(`qty_max_posted_value` IS NULL,0,1),
       `qty_max_posted_value`,
       `price_gbr_mode`,
       `price_gbr_custom_attribute`,
       `price_gbr_coefficient`,
       `price_euro_mode`,
       `price_euro_custom_attribute`,
       `price_euro_coefficient`,
       `price_variation_mode`
FROM `{$oldTable}`;

SQL
);

        $select = $this->installer->getConnection()->select()->from($oldTable, '*');
        $data = $this->installer->getConnection()->fetchAll($select);

        if (empty($data)) {
            return;
        }

        $migrationData = array();
        foreach ($data as $row) {
            $migrationData[(int)$row['template_selling_format_id']] = true;
        }

        $migrationTable = $this->installer->getTable('m2epro_migration_v6');
        $migrationTableData = array(
            'component' => 'play',
            'group' => 'selling_format_currencies',
            'data' => json_encode($migrationData)
        );
        $this->installer->getConnection()->insert($migrationTable, $migrationTableData);
    }

    //####################################

    private function processEbayTemplateReturnTable()
    {
        $newTable = $this->installer->getTable('m2epro_ebay_template_return');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  is_custom_template TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  accepted VARCHAR(255) NOT NULL,
  `option` VARCHAR(255) NOT NULL,
  within VARCHAR(255) NOT NULL,
  shipping_cost VARCHAR(255) NOT NULL,
  restocking_fee VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX is_custom_template (is_custom_template),
  INDEX marketplace_id (marketplace_id),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $templateGeneral = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_template_general');
        $ebayTemplateGeneral = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_template_general');

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `{$templateGeneral}`.`id`,
       `{$templateGeneral}`.`marketplace_id`,
       CONCAT(`{$templateGeneral}`.`title`, ' (return)'),
       0,
       `{$ebayTemplateGeneral}`.`refund_accepted`,
       `{$ebayTemplateGeneral}`.`refund_option`,
       `{$ebayTemplateGeneral}`.`refund_within`,
       `{$ebayTemplateGeneral}`.`refund_shippingcost`,
       `{$ebayTemplateGeneral}`.`refund_restockingfee`,
       `{$ebayTemplateGeneral}`.`refund_description`,
       `{$templateGeneral}`.`update_date`,
       `{$templateGeneral}`.`create_date`
FROM `{$ebayTemplateGeneral}`
INNER JOIN `{$templateGeneral}`
ON `{$templateGeneral}`.`id` = `{$ebayTemplateGeneral}`.`template_general_id`

SQL
);
    }

    //------------------------------------

    private function processEbayTemplatePaymentTable()
    {
        $newTable = $this->installer->getTable('m2epro_ebay_template_payment');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  is_custom_template TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  pay_pal_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  pay_pal_email_address VARCHAR(255) NOT NULL,
  pay_pal_immediate_payment TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX is_custom_template (is_custom_template),
  INDEX marketplace_id (marketplace_id),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $templateGeneral = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_template_general');
        $ebayTemplateGeneral = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_template_general');

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `{$templateGeneral}`.`id`,
       `{$templateGeneral}`.`marketplace_id`,
       CONCAT(`{$templateGeneral}`.`title`, ' (payment)'),
       0,
       IF(
           `{$ebayTemplateGeneral}`.`pay_pal_email_address` = '' OR
           `{$ebayTemplateGeneral}`.`pay_pal_email_address` IS NULL,
           0,
           1
       ),
       `{$ebayTemplateGeneral}`.`pay_pal_email_address`,
       `{$ebayTemplateGeneral}`.`pay_pal_immediate_payment`,
       `{$templateGeneral}`.`update_date`,
       `{$templateGeneral}`.`create_date`
FROM `{$ebayTemplateGeneral}`
INNER JOIN `{$templateGeneral}`
ON `{$templateGeneral}`.`id` = `{$ebayTemplateGeneral}`.`template_general_id`

SQL
);
    }

    private function processEbayTemplatePaymentServiceTable()
    {
        $newTable = $this->installer->getTable('m2epro_ebay_template_payment_service');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_template_general_payment');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  template_payment_id INT(11) UNSIGNED NOT NULL,
  code_name VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  INDEX template_payment_id (template_payment_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

INSERT INTO `{$newTable}` SELECT * FROM `{$oldTable}`

SQL
);
    }

    //------------------------------------

    private function processEbayTemplateShippingTable()
    {
        $newTable = $this->installer->getTable('m2epro_ebay_template_shipping');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  marketplace_id INT(11) UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  is_custom_template TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  country VARCHAR(255) NOT NULL,
  postal_code VARCHAR(255) NOT NULL,
  address VARCHAR(255) NOT NULL,
  vat_percent FLOAT UNSIGNED NOT NULL DEFAULT 0,
  dispatch_time INT(11) UNSIGNED NOT NULL DEFAULT 0,
  get_it_fast TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  tax_table_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  local_shipping_rate_table_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  international_shipping_rate_table_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  local_shipping_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  local_shipping_discount_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  local_shipping_combined_discount_profile_id VARCHAR(255) DEFAULT NULL,
  local_shipping_cash_on_delivery_cost_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  local_shipping_cash_on_delivery_cost_value VARCHAR(255) NOT NULL,
  local_shipping_cash_on_delivery_cost_attribute VARCHAR(255) NOT NULL,
  international_shipping_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  international_shipping_discount_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  international_shipping_combined_discount_profile_id VARCHAR(255) DEFAULT NULL,
  international_trade TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX international_trade (international_trade),
  INDEX is_custom_template (is_custom_template),
  INDEX marketplace_id (marketplace_id),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);
        $templateGeneral = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_template_general');
        $ebayTemplateGeneral = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_template_general');

        $data = $this->installer->getConnection()->query(<<<SQL
SELECT `{$templateGeneral}`.`id`,
       `{$templateGeneral}`.`marketplace_id`,
       CONCAT(`{$templateGeneral}`.`title`, ' (shipping)') AS `title`,
       0 AS `is_custom_template`,
       `{$ebayTemplateGeneral}`.`country`,
       `{$ebayTemplateGeneral}`.`postal_code`,
       `{$ebayTemplateGeneral}`.`address`,
       `{$ebayTemplateGeneral}`.`vat_percent`,
     IF(`{$ebayTemplateGeneral}`.`dispatch_time` = 7, 10, `{$ebayTemplateGeneral}`.`dispatch_time`) AS `dispatch_time`,
       `{$ebayTemplateGeneral}`.`get_it_fast`,
       `{$ebayTemplateGeneral}`.`use_ebay_tax_table` AS `tax_table_mode`,
       `{$ebayTemplateGeneral}`.`use_ebay_local_shipping_rate_table` AS `local_shipping_rate_table_mode`,
     `{$ebayTemplateGeneral}`.`use_ebay_international_shipping_rate_table` AS `international_shipping_rate_table_mode`,
       `{$ebayTemplateGeneral}`.`local_shipping_mode`,
       `{$ebayTemplateGeneral}`.`local_shipping_discount_mode`,
       `{$ebayTemplateGeneral}`.`local_shipping_combined_discount_profile_id`,
       `{$ebayTemplateGeneral}`.`local_shipping_cash_on_delivery_cost_mode`,
       `{$ebayTemplateGeneral}`.`local_shipping_cash_on_delivery_cost_value`,
       `{$ebayTemplateGeneral}`.`local_shipping_cash_on_delivery_cost_attribute`,
       `{$ebayTemplateGeneral}`.`international_shipping_mode`,
       `{$ebayTemplateGeneral}`.`international_shipping_discount_mode`,
       `{$ebayTemplateGeneral}`.`international_shipping_combined_discount_profile_id`,
       `{$ebayTemplateGeneral}`.`international_trade`,
       `{$templateGeneral}`.`update_date`,
       `{$templateGeneral}`.`create_date`
FROM `{$ebayTemplateGeneral}`
INNER JOIN `{$templateGeneral}`
ON `{$templateGeneral}`.`id` = `{$ebayTemplateGeneral}`.`template_general_id`
SQL
)->fetchAll();

        foreach ($data as &$row) {
            $internationalTrade = $row['international_trade'];
            $internationalTrade = json_decode($internationalTrade, true);

            $row['international_trade'] = 0;

            if (isset($internationalTrade['international_trade_na']) &&
                (int)$internationalTrade['international_trade_na'] == 1) {
                $row['international_trade'] = 1;
            }

            if (isset($internationalTrade['international_trade_uk']) &&
                (int)$internationalTrade['international_trade_uk'] == 1) {
                $row['international_trade'] = 2;
            }
        }

        !empty($data) && $this->installer->getConnection()->insertMultiple($newTable, $data);
    }

    private function processEbayTemplateShippingCalculatedTable()
    {
        $newTable = $this->installer->getTable('m2epro_ebay_template_shipping_calculated');
        $oldTable =
            $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_template_general_calculated_shipping');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  template_shipping_id INT(11) UNSIGNED NOT NULL,
  measurement_system TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  originating_postal_code VARCHAR(255) NOT NULL,
  package_size_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  package_size_value VARCHAR(500) NOT NULL,
  package_size_attribute VARCHAR(255) NOT NULL,
  dimension_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  dimension_width_value VARCHAR(500) NOT NULL,
  dimension_width_attribute VARCHAR(255) NOT NULL,
  dimension_height_value VARCHAR(500) NOT NULL,
  dimension_height_attribute VARCHAR(255) NOT NULL,
  dimension_depth_value VARCHAR(500) NOT NULL,
  dimension_depth_attribute VARCHAR(255) NOT NULL,
  weight_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  weight_minor VARCHAR(500) NOT NULL,
  weight_major VARCHAR(500) NOT NULL,
  weight_attribute VARCHAR(255) NOT NULL,
  local_handling_cost_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  local_handling_cost_value VARCHAR(255) NOT NULL,
  local_handling_cost_attribute VARCHAR(255) NOT NULL,
  international_handling_cost_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  international_handling_cost_value VARCHAR(255) NOT NULL,
  international_handling_cost_attribute VARCHAR(255) NOT NULL,
  PRIMARY KEY (template_shipping_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

INSERT INTO `{$newTable}` SELECT * FROM `{$oldTable}`

SQL
);
    }

    private function processEbayTemplateShippingServiceTable()
    {
        $newTable = $this->installer->getTable('m2epro_ebay_template_shipping_service');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_template_general_shipping');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  template_shipping_id INT(11) UNSIGNED NOT NULL,
  shipping_type TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  shipping_value VARCHAR(255) NOT NULL,
  cost_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  cost_value VARCHAR(255) NOT NULL,
  cost_additional_value VARCHAR(255) NOT NULL,
  locations TEXT NOT NULL,
  priority TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  INDEX priority (priority),
  INDEX template_shipping_id (template_shipping_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

INSERT INTO `{$newTable}`
SELECT `id`,
       `template_general_id`,
       `shipping_type`,
       `shipping_value`,
       `cost_mode`,
       `cost_value`,
       `cost_additional_items`,
       `locations`,
       `priority`
FROM `{$oldTable}`

SQL
);
    }

    //------------------------------------

    private function processEbayTemplateCategoryTable()
    {
        $newTable = $this->installer->getTable('m2epro_ebay_template_category');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  category_main_id INT(11) UNSIGNED NOT NULL,
  category_main_path VARCHAR(255) DEFAULT NULL,
  category_main_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 2,
  category_main_attribute VARCHAR(255) NOT NULL,
  category_secondary_id INT(11) UNSIGNED NOT NULL,
  category_secondary_path VARCHAR(255) DEFAULT NULL,
  category_secondary_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 2,
  category_secondary_attribute VARCHAR(255) NOT NULL,
  store_category_main_id DECIMAL(20, 0) UNSIGNED NOT NULL,
  store_category_main_path VARCHAR(255) DEFAULT NULL,
  store_category_main_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  store_category_main_attribute VARCHAR(255) NOT NULL,
  store_category_secondary_id DECIMAL(20, 0) UNSIGNED NOT NULL,
  store_category_secondary_path VARCHAR(255) DEFAULT NULL,
  store_category_secondary_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  store_category_secondary_attribute VARCHAR(255) NOT NULL,
  tax_category_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  tax_category_value VARCHAR(255) NOT NULL,
  tax_category_attribute VARCHAR(255) NOT NULL,
  variation_enabled TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  motors_specifics_attribute VARCHAR(255) DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);
        $templateGeneral = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_template_general');
        $ebayTemplateGeneral = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_template_general');

        $oldRows = $this->installer->getConnection()->query(<<<SQL
SELECT {$templateGeneral}.id,
       {$templateGeneral}.update_date,
       {$templateGeneral}.create_date,
       {$templateGeneral}.account_id,
       {$ebayTemplateGeneral}.*
FROM {$ebayTemplateGeneral}
INNER JOIN {$templateGeneral}
ON {$templateGeneral}.id = {$ebayTemplateGeneral}.template_general_id
SQL
        )->fetchAll();

        $newRows = array();
        foreach ($oldRows as $oldRow) {

            $newRow = array(
                'id' => $oldRow['id'],

                'category_main_id' => $oldRow['categories_main_id'],
                'category_main_path' => NULL,
                'category_main_mode' => $oldRow['categories_mode'],
                'category_main_attribute' => $oldRow['categories_main_attribute'],

                'category_secondary_id' => $oldRow['categories_secondary_id'],
                'category_secondary_path' => NULL,
                'category_secondary_mode' => $oldRow['categories_mode'],
                'category_secondary_attribute' => $oldRow['categories_secondary_attribute'],

                'store_category_main_id' => $oldRow['store_categories_main_id'],
                'store_category_main_path' => NULL,
                'store_category_main_mode' => $oldRow['store_categories_main_mode'],
                'store_category_main_attribute' => $oldRow['store_categories_main_attribute'],

                'store_category_secondary_id' => $oldRow['store_categories_secondary_id'],
                'store_category_secondary_path' => NULL,
                'store_category_secondary_mode' => $oldRow['store_categories_secondary_mode'],
                'store_category_secondary_attribute' => $oldRow['store_categories_secondary_attribute'],

                'tax_category_mode' => $oldRow['categories_mode'],
                'tax_category_value' => $oldRow['tax_category'],
                'tax_category_attribute' => $oldRow['tax_category_attribute'],

                'variation_enabled' => $oldRow['variation_enabled'],
                'motors_specifics_attribute' => $oldRow['motors_specifics_attribute'],

                'update_date' => $oldRow['update_date'],
                'create_date' => $oldRow['create_date'],
            );

            if ($newRow['category_main_mode'] == 0) {
                $newRow['category_main_mode'] = 1;
                $newRow['category_main_path'] = $this->getCategoryPathById($newRow['category_main_id']);
            } else {
                $newRow['category_main_mode'] = 2;
            }

            if ($newRow['category_secondary_mode'] == 0) {
                if ($newRow['category_secondary_id']) {
                    $newRow['category_secondary_mode'] = 1;
                    $newRow['category_secondary_path'] = $this->getCategoryPathById($newRow['category_secondary_id']);
                } else {
                    $newRow['category_secondary_mode'] = 0;
                }
            } else {
                if ($newRow['category_secondary_attribute']) {
                    $newRow['category_secondary_mode'] = 2;
                } else {
                    $newRow['category_secondary_mode'] = 0;
                }
            }

            if ($newRow['store_category_main_mode'] == 1) {
                $newRow['store_category_main_path'] = $this->getStoreCategoryPathById(
                    $newRow['store_category_main_id'],$oldRow['account_id']
                );
            }

            if ($newRow['store_category_secondary_mode'] == 1) {
                $newRow['store_category_secondary_path'] = $this->getStoreCategoryPathById(
                    $newRow['store_category_secondary_id'],$oldRow['account_id']
                );
            }

            if ($newRow['tax_category_mode'] == 0) {
                if ($newRow['tax_category_value']) {
                    $newRow['tax_category_mode'] = 1;
                } else {
                    $newRow['tax_category_mode'] = 0;
                }
            } else {
                if ($newRow['tax_category_attribute']) {
                    $newRow['tax_category_mode'] = 2;
                } else {
                    $newRow['tax_category_mode'] = 0;
                }
            }

            $newRows[] = $newRow;
        }

        !empty($newRows) && $this->installer->getConnection()->insertMultiple($newTable,$newRows);
    }

    private function processEbayTemplateCategorySpecificTable()
    {
        $newTable = $this->installer->getTable(
            'm2epro_ebay_template_category_specific'
        );
        $oldTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_template_general_specific'
        );

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  template_category_id INT(11) UNSIGNED NOT NULL,
  mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  mode_relation_id INT(11) UNSIGNED NOT NULL,
  attribute_id VARCHAR(255) NOT NULL,
  attribute_title VARCHAR(255) NOT NULL,
  value_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  value_ebay_recommended LONGTEXT DEFAULT NULL,
  value_custom_value VARCHAR(255) DEFAULT NULL,
  value_custom_attribute VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX template_category_id (template_category_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

INSERT INTO {$newTable} SELECT * FROM {$oldTable}

SQL
);
    }

    //------------------------------------

    private function createEbayTemplateDescriptionTable()
    {
        $newTable = $this->installer->getTable('m2epro_ebay_template_description');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  is_custom_template TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  title_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  title_template VARCHAR(255) NOT NULL,
  subtitle_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  subtitle_template VARCHAR(255) NOT NULL,
  description_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  description_template LONGTEXT NOT NULL,
  condition_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  condition_value INT(11) UNSIGNED NOT NULL DEFAULT 0,
  condition_attribute VARCHAR(255) NOT NULL,
  condition_note_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  condition_note_template TEXT NOT NULL,
  product_details TEXT DEFAULT NULL,
  cut_long_titles TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  hit_counter VARCHAR(255) NOT NULL,
  editor_type TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  enhancement VARCHAR(255) NOT NULL,
  gallery_type TINYINT(2) UNSIGNED NOT NULL DEFAULT 4,
  image_main_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  image_main_attribute VARCHAR(255) NOT NULL,
  gallery_images_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  gallery_images_limit TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  gallery_images_attribute VARCHAR(255) NOT NULL,
  variation_configurable_images VARCHAR(255) NOT NULL,
  use_supersize_images TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  watermark_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  watermark_image LONGBLOB DEFAULT NULL,
  watermark_settings TEXT DEFAULT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX is_custom_template (is_custom_template),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);
    }

    //####################################

    private function processListingTable()
    {
        $newTable = $this->installer->getTable('m2epro_listing');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listing');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(11) unsigned NOT NULL,
  `marketplace_id` int(11) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `store_id` int(11) unsigned NOT NULL,
  `products_total_count` int(11) unsigned NOT NULL DEFAULT '0',
  `products_active_count` int(11) unsigned NOT NULL DEFAULT '0',
  `products_inactive_count` int(11) unsigned NOT NULL DEFAULT '0',
  `items_active_count` int(11) unsigned NOT NULL DEFAULT '0',
  `source_products` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `categories_add_action` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `categories_delete_action` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `additional_data` TEXT DEFAULT NULL,
  `component_mode` varchar(10) DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`),
  KEY `component_mode` (`component_mode`),
  KEY `marketplace_id` (`marketplace_id`),
  KEY `store_id` (`store_id`),
  KEY `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $tmpTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_template_general');
        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `{$oldTable}`.`id`,
       `{$tmpTable}`.`account_id`,
       `{$tmpTable}`.`marketplace_id`,
       `{$oldTable}`.`title`,
       `{$oldTable}`.`store_id`,
       `{$oldTable}`.`products_total_count`,
       `{$oldTable}`.`products_active_count`,
       `{$oldTable}`.`products_inactive_count`,
       `{$oldTable}`.`items_active_count`,
       IF(`{$oldTable}`.`component_mode` = 'ebay', 1, `{$oldTable}`.`source_products`),
       IF(`{$oldTable}`.`component_mode` = 'ebay', 0,
        IF(`{$oldTable}`.`categories_add_action` = 2, 1, `{$oldTable}`.`categories_add_action`)),
       IF(`{$oldTable}`.`component_mode` = 'ebay', 0, `{$oldTable}`.`categories_delete_action`),
       NULL,
       `{$oldTable}`.`component_mode`,
       `{$oldTable}`.`update_date`,
       `{$oldTable}`.`create_date`
FROM `{$oldTable}`
INNER JOIN {$tmpTable}
ON {$tmpTable}.id = {$oldTable}.template_general_id;

SQL
);
    }

    //----------------------------------------------

    private function processEbayListingAndEbayTemplateDescriptionTables()
    {
        $newEbayListingTable = $this->installer->getTable('m2epro_ebay_listing');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newEbayListingTable};
CREATE TABLE {$newEbayListingTable} (
  listing_id INT(11) UNSIGNED NOT NULL,
  products_sold_count INT(11) UNSIGNED NOT NULL DEFAULT 0,
  items_sold_count INT(11) UNSIGNED NOT NULL DEFAULT 0,
  auto_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  auto_global_adding_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  auto_global_adding_template_category_id INT(11) UNSIGNED DEFAULT NULL,
  auto_website_adding_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  auto_website_adding_template_category_id INT(11) UNSIGNED DEFAULT NULL,
  auto_website_deleting_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  template_payment_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  template_payment_id INT(11) UNSIGNED DEFAULT NULL,
  template_payment_custom_id INT(11) UNSIGNED DEFAULT NULL,
  template_payment_policy_id INT(11) UNSIGNED DEFAULT NULL,
  template_shipping_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  template_shipping_id INT(11) UNSIGNED DEFAULT NULL,
  template_shipping_custom_id INT(11) UNSIGNED DEFAULT NULL,
  template_shipping_policy_id INT(11) UNSIGNED DEFAULT NULL,
  template_return_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  template_return_id INT(11) UNSIGNED DEFAULT NULL,
  template_return_custom_id INT(11) UNSIGNED DEFAULT NULL,
  template_return_policy_id INT(11) UNSIGNED DEFAULT NULL,
  template_description_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  template_description_id INT(11) UNSIGNED DEFAULT NULL,
  template_description_custom_id INT(11) UNSIGNED DEFAULT NULL,
  template_selling_format_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  template_selling_format_id INT(11) UNSIGNED DEFAULT NULL,
  template_selling_format_custom_id INT(11) UNSIGNED DEFAULT NULL,
  template_synchronization_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  template_synchronization_id INT(11) UNSIGNED DEFAULT NULL,
  template_synchronization_custom_id INT(11) UNSIGNED DEFAULT NULL,
  product_add_ids TEXT DEFAULT NULL,
  PRIMARY KEY (listing_id),
  INDEX auto_global_adding_mode (auto_global_adding_mode),
  INDEX auto_global_adding_template_category_id (auto_global_adding_template_category_id),
  INDEX auto_mode (auto_mode),
  INDEX auto_website_adding_mode (auto_website_adding_mode),
  INDEX auto_website_adding_template_category_id (auto_website_adding_template_category_id),
  INDEX auto_website_deleting_mode (auto_website_deleting_mode),
  INDEX items_sold_count (items_sold_count),
  INDEX products_sold_count (products_sold_count),
  INDEX template_description_custom_id (template_description_custom_id),
  INDEX template_description_id (template_description_id),
  INDEX template_description_mode (template_description_mode),
  INDEX template_payment_custom_id (template_payment_custom_id),
  INDEX template_payment_id (template_payment_id),
  INDEX template_payment_mode (template_payment_mode),
  INDEX template_payment_policy_id (template_payment_policy_id),
  INDEX template_return_custom_id (template_return_custom_id),
  INDEX template_return_id (template_return_id),
  INDEX template_return_mode (template_return_mode),
  INDEX template_return_policy_id (template_return_policy_id),
  INDEX template_selling_format_custom_id (template_selling_format_custom_id),
  INDEX template_selling_format_id (template_selling_format_id),
  INDEX template_selling_format_mode (template_selling_format_mode),
  INDEX template_shipping_custom_id (template_shipping_custom_id),
  INDEX template_shipping_id (template_shipping_id),
  INDEX template_shipping_mode (template_shipping_mode),
  INDEX template_shipping_policy_id (template_shipping_policy_id),
  INDEX template_synchronization_custom_id (template_synchronization_custom_id),
  INDEX template_synchronization_id (template_synchronization_id),
  INDEX template_synchronization_mode (template_synchronization_mode)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);
        $listingTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_listing'
        );
        $ebayListingTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_listing'
        );
        $ebayTemplateGeneralTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_template_general'
        );
        $templateDescriptionTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_template_description'
        );
        $ebayTemplateDescriptionTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_template_description'
        );

        $oldListingsData = $this->installer->getConnection()->query(<<<SQL
SELECT {$ebayListingTable}.listing_id,
       {$ebayListingTable}.products_sold_count,
       {$ebayListingTable}.items_sold_count,
       {$ebayTemplateDescriptionTable}.*,
       {$ebayTemplateGeneralTable}.*,
       {$templateDescriptionTable}.title,
       {$listingTable}.template_selling_format_id,
       {$listingTable}.template_synchronization_id
FROM {$ebayListingTable}
INNER JOIN {$listingTable}
ON {$listingTable}.id = {$ebayListingTable}.listing_id
INNER JOIN {$ebayTemplateDescriptionTable}
ON {$ebayTemplateDescriptionTable}.template_description_id = {$listingTable}.template_description_id
INNER JOIN {$templateDescriptionTable}
ON {$templateDescriptionTable}.id = {$ebayTemplateDescriptionTable}.template_description_id
INNER JOIN {$ebayTemplateGeneralTable}
ON {$ebayTemplateGeneralTable}.template_general_id = {$listingTable}.template_general_id
SQL
)->fetchAll();

        $newEbayTemplateDescriptionTable = $this->installer->getTable(
            'm2epro_ebay_template_description'
        );

        $listingsRows = array();
        $oldTemplatesDescriptionIds = array();
        $templatesDescriptionTempData = array();
        $templatesDescriptionTitlesCounter = array();

        foreach ($oldListingsData as $oldListingData) {

            $newProductDetails = $this->convertProductDetails($oldListingData['product_details']);

            $newConditionValue = (int)$oldListingData['condition_value'];
            if ($oldListingData['condition_mode'] == 0) {

                $tempValues = array(
                    1000,1500,1750,2000,2500,3000,4000,5000,6000,7000
                );

                if (!in_array($newConditionValue,$tempValues)) {
                    if ($newConditionValue < min($tempValues)) {
                        $newConditionValue = min($tempValues);
                    } else if ($newConditionValue > max($tempValues)) {
                        $newConditionValue = max($tempValues);
                    } else {
                        foreach ($tempValues as $i => $currentValue) {
                            $nextValue = isset($tempValues[$i + 1]) ? $tempValues[$i + 1] : false;

                            if (!$nextValue) {
                                break;
                            }

                            if ($newConditionValue > $currentValue && $newConditionValue < $nextValue) {
                                $newConditionValue = $currentValue;
                                break;
                            }
                        }
                    }
                }
            }

            $oldTemplatesDescriptionIds[] = (int)$oldListingData['template_description_id'];

            $key = array();
            $key[] = (int)$oldListingData['template_description_id'];
            $key[] = (string)$newProductDetails;
            $key[] = array('condition_mode' => (int)$oldListingData['condition_mode'],
                           'condition_value' => (int)$newConditionValue,
                           'condition_attribute' => (string)$oldListingData['condition_attribute']);
            $key[] = (string)$oldListingData['enhancement'];
            $key[] = (int)$oldListingData['gallery_type'];

            $key = md5(json_encode($key));

            if (isset($templatesDescriptionTempData[$key])) {
                $newTemplateDescriptionId = $templatesDescriptionTempData[$key];
            } else {

                $title = $oldListingData['title'];
                !isset($templatesDescriptionTitlesCounter[$title]) && $templatesDescriptionTitlesCounter[$title]= 0;

                ++$templatesDescriptionTitlesCounter[$title];

                if ($templatesDescriptionTitlesCounter[$title] > 1) {
                    $title .= ' ('.$templatesDescriptionTitlesCounter[$title].')';
                }

                $templateDescriptionData = array(
                    'title' => $title,
                    'is_custom_template' => 0,
                    'title_mode' => $oldListingData['title_mode'],
                    'title_template' => $oldListingData['title_template'],
                    'subtitle_mode' => $oldListingData['subtitle_mode'],
                    'subtitle_template' => $oldListingData['subtitle_template'],
                    'description_mode' => $oldListingData['description_mode'],
                    'description_template' => $oldListingData['description_template'],
                    'condition_mode' => $oldListingData['condition_mode'],
                    'condition_value' => $newConditionValue,
                    'condition_attribute' => $oldListingData['condition_attribute'],
                    'condition_note_mode' => 0,
                    'condition_note_template' => '',
                    'product_details' => $newProductDetails,
                    'cut_long_titles' => $oldListingData['cut_long_titles'],
                    'hit_counter' => $oldListingData['hit_counter'],
                    'editor_type' => $oldListingData['editor_type'],
                    'enhancement' => $oldListingData['enhancement'],
                    'gallery_type' => $oldListingData['gallery_type'],
                    'image_main_mode' => $oldListingData['image_main_mode'],
                    'image_main_attribute' => $oldListingData['image_main_attribute'],
                    'gallery_images_mode' => $oldListingData['gallery_images_mode'],
                    'gallery_images_limit' => $oldListingData['gallery_images_limit'],
                    'gallery_images_attribute' => $oldListingData['gallery_images_attribute'],
                    'variation_configurable_images' => $oldListingData['variation_configurable_images'],
                    'use_supersize_images' => $oldListingData['use_supersize_images'],
                    'watermark_mode' => $oldListingData['watermark_mode'],
                    'watermark_image' => $oldListingData['watermark_image'],
                    'watermark_settings' => $oldListingData['watermark_settings'],
                    'update_date' => Mage::getModel('core/date')->gmtDate(NULL),
                    'create_date' => Mage::getModel('core/date')->gmtDate(NULL),
                );

                $this->installer->getConnection()->insert($newEbayTemplateDescriptionTable,$templateDescriptionData);
                $newTemplateDescriptionId = $this->installer->getConnection()->lastInsertId();
                $templatesDescriptionTempData[$key] = $newTemplateDescriptionId;
            }

            $this->generalDescriptionCorrelation[(int)$oldListingData['template_general_id']][] =
                $newTemplateDescriptionId;

            $listingCategoryTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listing_category');
            $listingAutoMode = $this->installer->getConnection()->query(<<<SQL
SELECT IF(COUNT(*) = 0, 0, 3) FROM {$listingCategoryTable}
WHERE listing_id = {$oldListingData['listing_id']}
SQL
            )->fetchColumn();

            $listingsRows[] = array(
                'listing_id' => $oldListingData['listing_id'],
                'products_sold_count' => $oldListingData['products_sold_count'],
                'items_sold_count' => $oldListingData['items_sold_count'],

                'auto_mode' => $listingAutoMode,
                'auto_global_adding_mode' => 0,
                'auto_global_adding_template_category_id' => NULL,
                'auto_website_adding_mode' => 0,
                'auto_website_adding_template_category_id' => NULL,
                'auto_website_deleting_mode' => 0,

                'template_payment_mode' => 2,
                'template_payment_id' => $oldListingData['template_general_id'],
                'template_payment_custom_id' => NULL,
                'template_payment_policy_id' => NULL,

                'template_shipping_mode' => 2,
                'template_shipping_id' => $oldListingData['template_general_id'],
                'template_shipping_custom_id' => NULL,
                'template_shipping_policy_id' => NULL,

                'template_return_mode' => 2,
                'template_return_id' => $oldListingData['template_general_id'],
                'template_return_custom_id' => NULL,
                'template_return_policy_id' => NULL,

                'template_description_mode' => 2,
                'template_description_id' => $newTemplateDescriptionId,
                'template_description_custom_id' => NULL,

                'template_selling_format_mode' => 2,
                'template_selling_format_id' => $oldListingData['template_selling_format_id'],
                'template_selling_format_custom_id' => NULL,

                'template_synchronization_mode' => 2,
                'template_synchronization_id' => $oldListingData['template_synchronization_id'],
                'template_synchronization_custom_id' => NULL
            );

        }

        $oldTemplatesDescriptionIds = implode(',',array_unique($oldTemplatesDescriptionIds));

        if (!empty($oldTemplatesDescriptionIds)) {
            $unusedTemplatesDescription = $this->installer->getConnection()->query(<<<SQL
SELECT * FROM {$ebayTemplateDescriptionTable}
INNER JOIN {$templateDescriptionTable}
ON {$templateDescriptionTable}.id = {$ebayTemplateDescriptionTable}.template_description_id
WHERE {$templateDescriptionTable}.id NOT IN({$oldTemplatesDescriptionIds})
SQL
            )->fetchAll();

            foreach ($unusedTemplatesDescription as $templateDescription) {
                $templateDescriptionData = array(
                    'title' => $templateDescription['title'],
                    'is_custom_template' => 0,
                    'title_mode' => $templateDescription['title_mode'],
                    'title_template' => $templateDescription['title_template'],
                    'subtitle_mode' => $templateDescription['subtitle_mode'],
                    'subtitle_template' => $templateDescription['subtitle_template'],
                    'description_mode' => $templateDescription['description_mode'],
                    'description_template' => $templateDescription['description_template'],
                    'condition_mode' => 0,
                    'condition_value' => 1000,
                    'condition_attribute' => '',
                    'condition_note_mode' => 0,
                    'condition_note_template' => '',
                    'product_details' => json_encode(array(
                        'ean' => '', 'upc' => '', 'isbn' => '', 'epid' => '',
                    )),
                    'cut_long_titles' => $templateDescription['cut_long_titles'],
                    'hit_counter' => $templateDescription['hit_counter'],
                    'editor_type' => $templateDescription['editor_type'],
                    'enhancement' => '',
                    'gallery_type' => 0,
                    'image_main_mode' => $templateDescription['image_main_mode'],
                    'image_main_attribute' => $templateDescription['image_main_attribute'],
                    'gallery_images_mode' => $templateDescription['gallery_images_mode'],
                    'gallery_images_limit' => $templateDescription['gallery_images_limit'],
                    'gallery_images_attribute' => $templateDescription['gallery_images_attribute'],
                    'variation_configurable_images' => $templateDescription['variation_configurable_images'],
                    'use_supersize_images' => $templateDescription['use_supersize_images'],
                    'watermark_mode' => $templateDescription['watermark_mode'],
                    'watermark_image' => $templateDescription['watermark_image'],
                    'watermark_settings' => $templateDescription['watermark_settings'],
                    'update_date' => Mage::getModel('core/date')->gmtDate(NULL),
                    'create_date' => Mage::getModel('core/date')->gmtDate(NULL),
                );

                $this->installer->getConnection()->insert($newEbayTemplateDescriptionTable,$templateDescriptionData);
                $this->unusedTemplatesDescriptionIds[] = $this->installer->getConnection()->lastInsertId();
            }
        }

        !empty($listingsRows) && $this->installer->getConnection()->insertMultiple($newEbayListingTable,$listingsRows);

        $watermarksFolder = Mage::getBaseDir('var').'/M2ePro/ebay/template/description/watermarks/';
        if (!is_writable($watermarksFolder)) {
            return;
        }

        foreach (@scandir($watermarksFolder) as $folderItem) {
            if (!is_file($watermarksFolder.$folderItem)) {
                continue;
            }

            @unlink($watermarksFolder.$folderItem);
        }
    }

    private function processAmazonListingTable()
    {
        $newTable = $this->installer->getTable('m2epro_amazon_listing');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_amazon_listing');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `listing_id` int(11) unsigned NOT NULL,
  `template_selling_format_id` int(11) unsigned NOT NULL,
  `template_synchronization_id` int(11) unsigned NOT NULL,
  `sku_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `sku_custom_attribute` varchar(255) NOT NULL,
  `generate_sku_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `general_id_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `general_id_custom_attribute` varchar(255) NOT NULL,
  `worldwide_id_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `worldwide_id_custom_attribute` varchar(255) NOT NULL,
  `search_by_magento_title_mode` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `condition_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `condition_value` varchar(255) NOT NULL,
  `condition_custom_attribute` varchar(255) NOT NULL,
  `condition_note_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `condition_note_value` varchar(2000) NOT NULL,
  `condition_note_custom_attribute` varchar(255) NOT NULL,
  `handling_time_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `handling_time_value` int(11) unsigned NOT NULL DEFAULT '1',
  `handling_time_custom_attribute` varchar(255) NOT NULL,
  `restock_date_mode` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `restock_date_value` datetime NOT NULL,
  `restock_date_custom_attribute` varchar(255) NOT NULL,
  PRIMARY KEY (`listing_id`),
  KEY `template_selling_format_id` (`template_selling_format_id`),
  KEY `template_synchronization_id` (`template_synchronization_id`),
  KEY `generate_sku_mode` (`generate_sku_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $listingTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_listing'
        );
        $templateGeneralTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_amazon_template_general'
        );

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `{$oldTable}`.`listing_id`,

       `{$listingTable}`.`template_selling_format_id`,
       `{$listingTable}`.`template_synchronization_id`,

       `{$templateGeneralTable}`.`sku_mode`,
       `{$templateGeneralTable}`.`sku_custom_attribute`,
       `{$templateGeneralTable}`.`generate_sku_mode`,
       `{$templateGeneralTable}`.`general_id_mode`,
       `{$templateGeneralTable}`.`general_id_custom_attribute`,
       `{$templateGeneralTable}`.`worldwide_id_mode`,
       `{$templateGeneralTable}`.`worldwide_id_custom_attribute`,
       `{$templateGeneralTable}`.`search_by_magento_title_mode`,
       `{$templateGeneralTable}`.`condition_mode`,
       `{$templateGeneralTable}`.`condition_value`,
       `{$templateGeneralTable}`.`condition_custom_attribute`,
       `{$templateGeneralTable}`.`condition_note_mode`,
       `{$templateGeneralTable}`.`condition_note_value`,
       `{$templateGeneralTable}`.`condition_note_custom_attribute`,
       `{$templateGeneralTable}`.`handling_time_mode`,
       `{$templateGeneralTable}`.`handling_time_value`,
       `{$templateGeneralTable}`.`handling_time_custom_attribute`,
       `{$templateGeneralTable}`.`restock_date_mode`,
       `{$templateGeneralTable}`.`restock_date_value`,
       `{$templateGeneralTable}`.`restock_date_custom_attribute`

FROM `{$oldTable}`
INNER JOIN {$listingTable}
ON {$listingTable}.id = {$oldTable}.listing_id
INNER JOIN {$templateGeneralTable}
ON {$templateGeneralTable}.template_general_id = {$listingTable}.template_general_id;

SQL
);
    }

    private function processBuyListingTable()
    {
        $newTable = $this->installer->getTable('m2epro_buy_listing');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_buy_listing');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `listing_id` int(11) unsigned NOT NULL,
  `template_selling_format_id` int(11) unsigned NOT NULL,
  `template_synchronization_id` int(11) unsigned NOT NULL,
  `sku_custom_attribute` varchar(255) NOT NULL,
  `generate_sku_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `general_id_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `general_id_custom_attribute` varchar(255) NOT NULL,
  `search_by_magento_title_mode` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `condition_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `condition_value` varchar(255) NOT NULL,
  `condition_custom_attribute` varchar(255) NOT NULL,
  `condition_note_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `condition_note_value` text NOT NULL,
  `condition_note_custom_attribute` varchar(255) NOT NULL,
  `shipping_standard_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `shipping_standard_value` decimal(12,4) unsigned NOT NULL,
  `shipping_standard_custom_attribute` varchar(255) NOT NULL,
  `shipping_expedited_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `shipping_expedited_value` decimal(12,4) unsigned NOT NULL,
  `shipping_expedited_custom_attribute` varchar(255) NOT NULL,
  `shipping_one_day_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `shipping_one_day_value` decimal(12,4) unsigned NOT NULL,
  `shipping_one_day_custom_attribute` varchar(255) NOT NULL,
  `shipping_two_day_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `shipping_two_day_value` decimal(12,4) unsigned NOT NULL,
  `shipping_two_day_custom_attribute` varchar(255) NOT NULL,
  `sku_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`listing_id`),
  KEY `template_selling_format_id` (`template_selling_format_id`),
  KEY `template_synchronization_id` (`template_synchronization_id`),
  KEY `generate_sku_mode` (`generate_sku_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $listingTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_listing'
        );
        $templateGeneralTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_buy_template_general'
        );

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `{$oldTable}`.`listing_id`,

       `{$listingTable}`.`template_selling_format_id`,
       `{$listingTable}`.`template_synchronization_id`,

       `{$templateGeneralTable}`.`sku_custom_attribute`,
       `{$templateGeneralTable}`.`generate_sku_mode`,
       `{$templateGeneralTable}`.`general_id_mode`,
       `{$templateGeneralTable}`.`general_id_custom_attribute`,
       `{$templateGeneralTable}`.`search_by_magento_title_mode`,
       `{$templateGeneralTable}`.`condition_mode`,
       `{$templateGeneralTable}`.`condition_value`,
       `{$templateGeneralTable}`.`condition_custom_attribute`,
       `{$templateGeneralTable}`.`condition_note_mode`,
       `{$templateGeneralTable}`.`condition_note_value`,
       `{$templateGeneralTable}`.`condition_note_custom_attribute`,
       `{$templateGeneralTable}`.`shipping_standard_mode`,
       `{$templateGeneralTable}`.`shipping_standard_value`,
       `{$templateGeneralTable}`.`shipping_standard_custom_attribute`,
       `{$templateGeneralTable}`.`shipping_expedited_mode`,
       `{$templateGeneralTable}`.`shipping_expedited_value`,
       `{$templateGeneralTable}`.`shipping_expedited_custom_attribute`,
       `{$templateGeneralTable}`.`shipping_one_day_mode`,
       `{$templateGeneralTable}`.`shipping_one_day_value`,
       `{$templateGeneralTable}`.`shipping_one_day_custom_attribute`,
       `{$templateGeneralTable}`.`shipping_two_day_mode`,
       `{$templateGeneralTable}`.`shipping_two_day_value`,
       `{$templateGeneralTable}`.`shipping_two_day_custom_attribute`,
       `{$templateGeneralTable}`.`sku_mode`

FROM `{$oldTable}`
INNER JOIN {$listingTable}
ON {$listingTable}.id = {$oldTable}.listing_id
INNER JOIN {$templateGeneralTable}
ON {$templateGeneralTable}.template_general_id = {$listingTable}.template_general_id;

SQL
);
    }

    private function processPlayListingTable()
    {
        $newTable = $this->installer->getTable('m2epro_play_listing');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_play_listing');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `listing_id` int(11) unsigned NOT NULL,
  `template_selling_format_id` int(11) unsigned NOT NULL,
  `template_synchronization_id` int(11) unsigned NOT NULL,
  `sku_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `sku_custom_attribute` varchar(255) NOT NULL,
  `generate_sku_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `general_id_mode` varchar(255) NOT NULL,
  `general_id_custom_attribute` varchar(255) NOT NULL,
  `search_by_magento_title_mode` tinyint(2) unsigned NOT NULL DEFAULT '1',
  `dispatch_to_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `dispatch_to_value` varchar(255) NOT NULL,
  `dispatch_to_custom_attribute` varchar(255) NOT NULL,
  `dispatch_from_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `dispatch_from_value` varchar(255) NOT NULL,
  `shipping_price_gbr_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `shipping_price_gbr_value` decimal(12,2) unsigned NOT NULL,
  `shipping_price_gbr_custom_attribute` varchar(255) NOT NULL,
  `shipping_price_euro_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `shipping_price_euro_value` decimal(12,2) unsigned NOT NULL,
  `shipping_price_euro_custom_attribute` varchar(255) NOT NULL,
  `condition_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `condition_value` varchar(255) NOT NULL,
  `condition_custom_attribute` varchar(255) NOT NULL,
  `condition_note_mode` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `condition_note_value` text NOT NULL,
  `condition_note_custom_attribute` varchar(255) NOT NULL,
  PRIMARY KEY (`listing_id`),
  KEY `template_selling_format_id` (`template_selling_format_id`),
  KEY `template_synchronization_id` (`template_synchronization_id`),
  KEY `generate_sku_mode` (`generate_sku_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $listingTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_listing'
        );
        $templateGeneralTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_play_template_general'
        );

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `{$oldTable}`.`listing_id`,

       `{$listingTable}`.`template_selling_format_id`,
       `{$listingTable}`.`template_synchronization_id`,

       `{$templateGeneralTable}`.`sku_mode`,
       `{$templateGeneralTable}`.`sku_custom_attribute`,
       `{$templateGeneralTable}`.`generate_sku_mode`,
       `{$templateGeneralTable}`.`general_id_mode`,
       `{$templateGeneralTable}`.`general_id_custom_attribute`,
       `{$templateGeneralTable}`.`search_by_magento_title_mode`,
       `{$templateGeneralTable}`.`dispatch_to_mode`,
       `{$templateGeneralTable}`.`dispatch_to_value`,
       `{$templateGeneralTable}`.`dispatch_to_custom_attribute`,
       `{$templateGeneralTable}`.`dispatch_from_mode`,
       `{$templateGeneralTable}`.`dispatch_from_value`,
       `{$templateGeneralTable}`.`shipping_price_gbr_mode`,
       `{$templateGeneralTable}`.`shipping_price_gbr_value`,
       `{$templateGeneralTable}`.`shipping_price_gbr_custom_attribute`,
       `{$templateGeneralTable}`.`shipping_price_euro_mode`,
       `{$templateGeneralTable}`.`shipping_price_euro_value`,
       `{$templateGeneralTable}`.`shipping_price_euro_custom_attribute`,
       `{$templateGeneralTable}`.`condition_mode`,
       `{$templateGeneralTable}`.`condition_value`,
       `{$templateGeneralTable}`.`condition_custom_attribute`,
       `{$templateGeneralTable}`.`condition_note_mode`,
       `{$templateGeneralTable}`.`condition_note_value`,
       `{$templateGeneralTable}`.`condition_note_custom_attribute`

FROM `{$oldTable}`
INNER JOIN {$listingTable}
ON {$listingTable}.id = {$oldTable}.listing_id
INNER JOIN {$templateGeneralTable}
ON {$templateGeneralTable}.template_general_id = {$listingTable}.template_general_id;

SQL
);
    }

    //####################################

    private function createListingProductTable()
    {
        $newTable = $this->installer->getTable('m2epro_listing_product');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `listing_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `status_changer` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `component_mode` varchar(10) DEFAULT NULL,
  `additional_data` text,
  `tried_to_list` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_need_synchronize` tinyint(2) UNSIGNED NOT NULL DEFAULT '0',
  `synch_reasons` TEXT NULL DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `component_mode` (`component_mode`),
  KEY `listing_id` (`listing_id`),
  KEY `product_id` (`product_id`),
  KEY `status` (`status`),
  KEY `status_changer` (`status_changer`),
  KEY `tried_to_list` (`tried_to_list`),
  KEY `is_need_synchronize` (`is_need_synchronize`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);
    }

    //------------------------------------

    private function processEbayListingProductTable()
    {
        $newTable = $this->installer->getTable('m2epro_ebay_listing_product');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_listing_product');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  listing_product_id INT(11) UNSIGNED NOT NULL,
  template_category_id INT(11) UNSIGNED DEFAULT NULL,
  ebay_item_id INT(11) UNSIGNED DEFAULT NULL,
  online_start_price DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_reserve_price DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_buyitnow_price DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  online_qty INT(11) UNSIGNED DEFAULT NULL,
  online_qty_sold INT(11) UNSIGNED DEFAULT NULL,
  online_bids INT(11) UNSIGNED DEFAULT NULL,
  online_category VARCHAR(255) DEFAULT NULL,
  start_date DATETIME DEFAULT NULL,
  end_date DATETIME DEFAULT NULL,
  is_m2epro_listed_item TINYINT(2) UNSIGNED DEFAULT NULL,
  template_payment_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  template_payment_id INT(11) UNSIGNED DEFAULT NULL,
  template_payment_custom_id INT(11) UNSIGNED DEFAULT NULL,
  template_payment_policy_id INT(11) UNSIGNED DEFAULT NULL,
  template_shipping_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  template_shipping_id INT(11) UNSIGNED DEFAULT NULL,
  template_shipping_custom_id INT(11) UNSIGNED DEFAULT NULL,
  template_shipping_policy_id INT(11) UNSIGNED DEFAULT NULL,
  template_return_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  template_return_id INT(11) UNSIGNED DEFAULT NULL,
  template_return_custom_id INT(11) UNSIGNED DEFAULT NULL,
  template_return_policy_id INT(11) UNSIGNED DEFAULT NULL,
  template_description_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  template_description_id INT(11) UNSIGNED DEFAULT NULL,
  template_description_custom_id INT(11) UNSIGNED DEFAULT NULL,
  template_selling_format_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  template_selling_format_id INT(11) UNSIGNED DEFAULT NULL,
  template_selling_format_custom_id INT(11) UNSIGNED DEFAULT NULL,
  template_synchronization_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  template_synchronization_id INT(11) UNSIGNED DEFAULT NULL,
  template_synchronization_custom_id INT(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (listing_product_id),
  INDEX ebay_item_id (ebay_item_id),
  INDEX end_date (end_date),
  INDEX is_m2epro_listed_item (is_m2epro_listed_item),
  INDEX online_bids (online_bids),
  INDEX online_buyitnow_price (online_buyitnow_price),
  INDEX online_category (online_category),
  INDEX online_qty (online_qty),
  INDEX online_qty_sold (online_qty_sold),
  INDEX online_reserve_price (online_reserve_price),
  INDEX online_start_price (online_start_price),
  INDEX start_date (start_date),
  INDEX template_category_id (template_category_id),
  INDEX template_description_custom_id (template_description_custom_id),
  INDEX template_description_id (template_description_id),
  INDEX template_description_mode (template_description_mode),
  INDEX template_payment_custom_id (template_payment_custom_id),
  INDEX template_payment_id (template_payment_id),
  INDEX template_payment_mode (template_payment_mode),
  INDEX template_payment_policy_id (template_payment_policy_id),
  INDEX template_return_custom_id (template_return_custom_id),
  INDEX template_return_id (template_return_id),
  INDEX template_return_mode (template_return_mode),
  INDEX template_return_policy_id (template_return_policy_id),
  INDEX template_selling_format_custom_id (template_selling_format_custom_id),
  INDEX template_selling_format_id (template_selling_format_id),
  INDEX template_selling_format_mode (template_selling_format_mode),
  INDEX template_shipping_custom_id (template_shipping_custom_id),
  INDEX template_shipping_id (template_shipping_id),
  INDEX template_shipping_mode (template_shipping_mode),
  INDEX template_shipping_policy_id (template_shipping_policy_id),
  INDEX template_synchronization_custom_id (template_synchronization_custom_id),
  INDEX template_synchronization_id (template_synchronization_id),
  INDEX template_synchronization_mode (template_synchronization_mode)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $listingTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listing');
        $listingProductTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listing_product');

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO {$newTable}
SELECT {$oldTable}.listing_product_id,
       {$listingTable}.template_general_id,
       {$oldTable}.ebay_item_id,
       {$oldTable}.online_start_price,
       {$oldTable}.online_reserve_price,
       {$oldTable}.online_buyitnow_price,
       {$oldTable}.online_qty,
       {$oldTable}.online_qty_sold,
       {$oldTable}.online_bids,
       NULL,
       {$oldTable}.start_date,
       {$oldTable}.end_date,
       {$oldTable}.is_m2epro_listed_item,
      0,
      NULL,
      NULL,
      NULL,
      0,
      NULL,
      NULL,
      NULL,
      0,
      NULL,
      NULL,
      NULL,
      0,
      NULL,
      NULL,
      0,
      NULL,
      NULL,
      0,
      NULL,
      NULL
FROM {$oldTable}
INNER JOIN {$listingProductTable}
ON {$listingProductTable}.id = {$oldTable}.listing_product_id
INNER JOIN {$listingTable}
ON {$listingTable}.id = {$listingProductTable}.listing_id

SQL
);

        $newLPTable = $this->installer->getTable('m2epro_listing_product');
        $oldLPTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listing_product');

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newLPTable}`
SELECT `{$oldLPTable}`.`id`,
       `{$oldLPTable}`.`listing_id`,
       `{$oldLPTable}`.`product_id`,
       `{$oldLPTable}`.`status`,
       `{$oldLPTable}`.`status_changer`,
       `{$oldLPTable}`.`component_mode`,
       `{$oldTable}`.`additional_data`,
       `{$oldTable}`.`tried_to_list`,
       0,
       NULL,
       `{$oldLPTable}`.`create_date`,
       `{$oldLPTable}`.`update_date`
FROM `{$oldTable}`
INNER JOIN `{$oldLPTable}`
ON `{$oldLPTable}`.`id` = `{$oldTable}`.`listing_product_id`

SQL
);
    }

    private function processAmazonListingProductTable()
    {
        $newTable = $this->installer->getTable('m2epro_amazon_listing_product');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_amazon_listing_product');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `listing_product_id` int(11) unsigned NOT NULL,
  `template_new_product_id` int(11) unsigned DEFAULT NULL,
  `is_variation_product` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_variation_matched` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `general_id` varchar(255) DEFAULT NULL,
  `general_id_search_status` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `general_id_search_suggest_data` text,
  `worldwide_id` varchar(255) DEFAULT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `online_price` decimal(12,4) unsigned DEFAULT NULL,
  `online_sale_price` decimal(12,4) unsigned DEFAULT NULL,
  `online_qty` int(11) unsigned DEFAULT NULL,
  `is_afn_channel` tinyint(2) unsigned DEFAULT NULL,
  `is_isbn_general_id` tinyint(2) unsigned DEFAULT NULL,
  `is_upc_worldwide_id` tinyint(2) unsigned DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  PRIMARY KEY (`listing_product_id`),
  KEY `end_date` (`end_date`),
  KEY `general_id` (`general_id`),
  KEY `general_id_search_status` (`general_id_search_status`),
  KEY `is_afn_channel` (`is_afn_channel`),
  KEY `is_isbn_general_id` (`is_isbn_general_id`),
  KEY `is_upc_worldwide_id` (`is_upc_worldwide_id`),
  KEY `online_price` (`online_price`),
  KEY `online_qty` (`online_qty`),
  KEY `online_sale_price` (`online_sale_price`),
  KEY `sku` (`sku`),
  KEY `start_date` (`start_date`),
  KEY `template_new_product_id` (`template_new_product_id`),
  KEY `worldwide_id` (`worldwide_id`),
  KEY `is_variation_product` (`is_variation_product`),
  KEY `is_variation_matched` (`is_variation_matched`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `listing_product_id`,
       `template_new_product_id`,
       `is_variation_product`,
       `is_variation_matched`,
       `general_id`,
       `general_id_search_status`,
       `general_id_search_suggest_data`,
       `worldwide_id`,
       `sku`,
       `online_price`,
       `online_sale_price`,
       `online_qty`,
       `is_afn_channel`,
       `is_isbn_general_id`,
       `is_upc_worldwide_id`,
       `start_date`,
       `end_date`
FROM `{$oldTable}`;

SQL
);

        $newLPTable = $this->installer->getTable('m2epro_listing_product');
        $oldLPTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listing_product');

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newLPTable}`
SELECT `{$oldLPTable}`.`id`,
       `{$oldLPTable}`.`listing_id`,
       `{$oldLPTable}`.`product_id`,
       `{$oldLPTable}`.`status`,
       `{$oldLPTable}`.`status_changer`,
       `{$oldLPTable}`.`component_mode`,
       `{$oldTable}`.`additional_data`,
       `{$oldTable}`.`tried_to_list`,
       0,
       NULL,
       `{$oldLPTable}`.`create_date`,
       `{$oldLPTable}`.`update_date`
FROM `{$oldTable}`
INNER JOIN `{$oldLPTable}`
ON `{$oldLPTable}`.`id` = `{$oldTable}`.`listing_product_id`

SQL
);
    }

    private function processBuyListingProductTable()
    {
        $newTable = $this->installer->getTable('m2epro_buy_listing_product');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_buy_listing_product');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `listing_product_id` int(11) unsigned NOT NULL,
  `template_new_product_id` int(11) unsigned DEFAULT NULL,
  `is_variation_product` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_variation_matched` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `general_id` int(11) unsigned DEFAULT NULL,
  `general_id_search_status` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `general_id_search_suggest_data` text,
  `sku` varchar(255) DEFAULT NULL,
  `online_price` decimal(12,4) unsigned DEFAULT NULL,
  `online_qty` int(11) unsigned DEFAULT NULL,
  `condition` tinyint(4) unsigned DEFAULT NULL,
  `condition_note` varchar(255) DEFAULT NULL,
  `shipping_standard_rate` decimal(12,4) unsigned DEFAULT NULL,
  `shipping_expedited_mode` tinyint(2) unsigned DEFAULT NULL,
  `shipping_expedited_rate` decimal(12,4) unsigned DEFAULT NULL,
  `ignore_next_inventory_synch` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  PRIMARY KEY (`listing_product_id`),
  KEY `condition` (`condition`),
  KEY `end_date` (`end_date`),
  KEY `general_id` (`general_id`),
  KEY `general_id_search_status` (`general_id_search_status`),
  KEY `ignore_next_inventory_synch` (`ignore_next_inventory_synch`),
  KEY `online_price` (`online_price`),
  KEY `online_qty` (`online_qty`),
  KEY `shipping_expedited_mode` (`shipping_expedited_mode`),
  KEY `shipping_expedited_rate` (`shipping_expedited_rate`),
  KEY `shipping_standard_rate` (`shipping_standard_rate`),
  KEY `sku` (`sku`),
  KEY `start_date` (`start_date`),
  KEY `template_new_product_id` (`template_new_product_id`),
  KEY `is_variation_product` (`is_variation_product`),
  KEY `is_variation_matched` (`is_variation_matched`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `listing_product_id`,
       `template_new_product_id`,
       `is_variation_product`,
       `is_variation_matched`,
       `general_id`,
       `general_id_search_status`,
       `general_id_search_suggest_data`,
       `sku`,
       `online_price`,
       `online_qty`,
       `condition`,
       `condition_note`,
       `shipping_standard_rate`,
       `shipping_expedited_mode`,
       `shipping_expedited_rate`,
       `ignore_next_inventory_synch`,
       `start_date`,
       `end_date`
FROM `{$oldTable}`;

SQL
);

        $newLPTable = $this->installer->getTable('m2epro_listing_product');
        $oldLPTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listing_product');

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newLPTable}`
SELECT `{$oldLPTable}`.`id`,
       `{$oldLPTable}`.`listing_id`,
       `{$oldLPTable}`.`product_id`,
       `{$oldLPTable}`.`status`,
       `{$oldLPTable}`.`status_changer`,
       `{$oldLPTable}`.`component_mode`,
       `{$oldTable}`.`additional_data`,
       `{$oldTable}`.`tried_to_list`,
       0,
       NULL,
       `{$oldLPTable}`.`create_date`,
       `{$oldLPTable}`.`update_date`
FROM `{$oldTable}`
INNER JOIN `{$oldLPTable}`
ON `{$oldLPTable}`.`id` = `{$oldTable}`.`listing_product_id`

SQL
);
    }

    private function processPlayListingProductTable()
    {
        $newTable = $this->installer->getTable('m2epro_play_listing_product');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_play_listing_product');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `listing_product_id` int(11) unsigned NOT NULL,
  `general_id` varchar(20) DEFAULT NULL,
  `general_id_type` varchar(255) DEFAULT NULL,
  `is_variation_product` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_variation_matched` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `play_listing_id` int(11) unsigned DEFAULT NULL,
  `link_info` varchar(255) DEFAULT NULL,
  `general_id_search_status` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `general_id_search_suggest_data` text,
  `sku` varchar(255) DEFAULT NULL,
  `dispatch_to` varchar(255) DEFAULT NULL,
  `dispatch_from` varchar(255) DEFAULT NULL,
  `online_price_gbr` decimal(12,4) unsigned DEFAULT NULL,
  `online_price_euro` decimal(12,4) unsigned DEFAULT NULL,
  `online_shipping_price_gbr` decimal(12,4) unsigned DEFAULT NULL,
  `online_shipping_price_euro` decimal(12,4) unsigned DEFAULT NULL,
  `online_qty` int(11) unsigned DEFAULT NULL,
  `condition` varchar(255) DEFAULT NULL,
  `condition_note` varchar(255) DEFAULT NULL,
  `ignore_next_inventory_synch` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  PRIMARY KEY (`listing_product_id`),
  KEY `condition` (`condition`),
  KEY `dispatch_from` (`dispatch_from`),
  KEY `dispatch_to` (`dispatch_to`),
  KEY `end_date` (`end_date`),
  KEY `general_id` (`general_id`),
  KEY `general_id_search_status` (`general_id_search_status`),
  KEY `general_id_type` (`general_id_type`),
  KEY `ignore_next_inventory_synch` (`ignore_next_inventory_synch`),
  KEY `online_price_euro` (`online_price_euro`),
  KEY `online_price_gbr` (`online_price_gbr`),
  KEY `online_qty` (`online_qty`),
  KEY `online_shipping_price_euro` (`online_shipping_price_euro`),
  KEY `online_shipping_price_gbr` (`online_shipping_price_gbr`),
  KEY `play_listing_id` (`play_listing_id`),
  KEY `sku` (`sku`),
  KEY `start_date` (`start_date`),
  KEY `is_variation_product` (`is_variation_product`),
  KEY `is_variation_matched` (`is_variation_matched`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `listing_product_id`,
       `general_id`,
       `general_id_type`,
       `is_variation_product`,
       `is_variation_matched`,
       `play_listing_id`,
       `link_info`,
       `general_id_search_status`,
       `general_id_search_suggest_data`,
       `sku`,
       `dispatch_to`,
       `dispatch_from`,
       `online_price_gbr`,
       `online_price_euro`,
       `online_shipping_price_gbr`,
       `online_shipping_price_euro`,
       `online_qty`,
       `condition`,
       `condition_note`,
       `ignore_next_inventory_synch`,
       `start_date`,
       `end_date`
FROM `{$oldTable}`;

SQL
);

        $newLPTable = $this->installer->getTable('m2epro_listing_product');
        $oldLPTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listing_product');

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newLPTable}`
SELECT `{$oldLPTable}`.`id`,
       `{$oldLPTable}`.`listing_id`,
       `{$oldLPTable}`.`product_id`,
       `{$oldLPTable}`.`status`,
       `{$oldLPTable}`.`status_changer`,
       `{$oldLPTable}`.`component_mode`,
       `{$oldTable}`.`additional_data`,
       `{$oldTable}`.`tried_to_list`,
       0,
       NULL,
       `{$oldLPTable}`.`create_date`,
       `{$oldLPTable}`.`update_date`
FROM `{$oldTable}`
INNER JOIN `{$oldLPTable}`
ON `{$oldLPTable}`.`id` = `{$oldTable}`.`listing_product_id`

SQL
);
    }

    //####################################

    private function processListingProductVariationTable()
    {
        $newTable = $this->installer->getTable('m2epro_listing_product_variation');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listing_product_variation');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `listing_product_id` int(11) unsigned NOT NULL,
  `component_mode` varchar(10) DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `component_mode` (`component_mode`),
  KEY `listing_product_id` (`listing_product_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `id`,
       `listing_product_id`,
       `component_mode`,
       `update_date`,
       `create_date`
FROM `{$oldTable}`;

SQL
);
    }

    //-----------------------------------

    private function processEbayListingProductVariationTable()
    {
        $newTable = $this->installer->getTable('m2epro_ebay_listing_product_variation');
        $oldTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_listing_product_variation');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `listing_product_variation_id` int(11) unsigned NOT NULL,
  `add` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `delete` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `online_price` decimal(12,4) unsigned DEFAULT NULL,
  `online_qty` int(11) unsigned DEFAULT NULL,
  `online_qty_sold` int(11) unsigned DEFAULT NULL,
  `status` tinyint(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`listing_product_variation_id`),
  KEY `add` (`add`),
  KEY `delete` (`delete`),
  KEY `online_price` (`online_price`),
  KEY `online_qty` (`online_qty`),
  KEY `online_qty_sold` (`online_qty_sold`),
  KEY `status` (`status`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $tmpTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listing_product_variation');
        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newTable}`
SELECT `{$oldTable}`.`listing_product_variation_id`,
       `{$tmpTable}`.`add`,
       `{$tmpTable}`.`delete`,
       `{$oldTable}`.`online_price`,
       `{$oldTable}`.`online_qty`,
       `{$oldTable}`.`online_qty_sold`,
       `{$tmpTable}`.`status`
FROM `{$oldTable}`
INNER JOIN `{$tmpTable}`
ON `{$tmpTable}`.`id` = `{$oldTable}`.`listing_product_variation_id`;

SQL
);
    }

    //####################################

    private function processEbayMarketplaceTable()
    {
        $newTable = $this->installer->getTable('m2epro_ebay_marketplace');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  `marketplace_id` int(11) unsigned NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `categories_version` int(11) unsigned NOT NULL DEFAULT '0',
  `is_multivariation` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_freight_shipping` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_calculated_shipping` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_tax` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_vat` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_local_shipping_rate_table` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_international_shipping_rate_table` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_get_it_fast` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_english_measurement_system` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_metric_measurement_system` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `is_cash_on_delivery` tinyint(2) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`marketplace_id`),
  KEY `is_calculated_shipping` (`is_calculated_shipping`),
  KEY `is_cash_on_delivery` (`is_cash_on_delivery`),
  KEY `is_english_measurement_system` (`is_english_measurement_system`),
  KEY `is_metric_measurement_system` (`is_metric_measurement_system`),
  KEY `is_freight_shipping` (`is_freight_shipping`),
  KEY `is_get_it_fast` (`is_get_it_fast`),
  KEY `is_international_shipping_rate_table` (`is_international_shipping_rate_table`),
  KEY `is_local_shipping_rate_table` (`is_local_shipping_rate_table`),
  KEY `is_tax` (`is_tax`),
  KEY `is_vat` (`is_vat`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO {$newTable} VALUES
  (1, 'USD', 0, 1, 1, 1, 1, 0, 1, 0, 1, 1, 0, 0),
  (2, 'CAD', 0, 1, 0, 1, 1, 0, 0, 0, 0, 1, 1, 0),
  (3, 'GBP', 0, 1, 1, 0, 0, 1, 1, 1, 1, 0, 1, 0),
  (4, 'AUD', 0, 1, 1, 1, 0, 0, 0, 0, 1, 0, 1, 0),
  (5, 'EUR', 0, 1, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0),
  (6, 'EUR', 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0),
  (7, 'EUR', 0, 1, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0),
  (8, 'EUR', 0, 1, 0, 0, 0, 1, 1, 1, 1, 0, 1, 0),
  (9, 'USD', 0, 1, 0, 1, 1, 0, 0, 0, 1, 1, 0, 0),
  (10, 'EUR', 0, 1, 0, 0, 0, 1, 0, 0, 1, 0, 1, 1),
  (11, 'EUR', 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0),
  (12, 'EUR', 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0),
  (13, 'EUR', 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0),
  (14, 'CHF', 0, 1, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0),
  (15, 'HKD', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0),
  (16, 'INR', 0, 1, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0),
  (17, 'EUR', 0, 1, 0, 0, 0, 1, 0, 0, 1, 0, 1, 0),
  (18, 'MYR', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0),
  (19, 'CAD', 0, 0, 0, 1, 1, 0, 0, 0, 0, 1, 1, 0),
  (20, 'PHP', 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0),
  (21, 'PLN', 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 0),
  (22, 'SGD', 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 0),
  (23, 'SEK', 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 0);

SQL
);
    }

    private function processEbayListingAutoCategoryTable()
    {
        $newTable = $this->installer->getTable('m2epro_ebay_listing_auto_category');
        $newGroupTable = $this->installer->getTable('m2epro_ebay_listing_auto_category_group');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  listing_id INT(11) UNSIGNED NOT NULL,
  group_id INT(11) UNSIGNED NOT NULL,
  category_id INT(11) UNSIGNED NOT NULL,
  adding_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  adding_template_category_id INT(11) UNSIGNED DEFAULT NULL,
  adding_duplicate TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  deleting_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX adding_template_category_id (adding_template_category_id),
  INDEX category_id (category_id),
  INDEX listing_id (listing_id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS {$newGroupTable};
CREATE TABLE {$newGroupTable} (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  listing_id INT(11) UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX listing_id (listing_id),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);

        $listingTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listing');
        $listingCategoryTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listing_category');

        $this->installer->getConnection()->multi_query(<<<SQL

INSERT INTO `{$newGroupTable}`
SELECT `{$listingTable}`.`id`,
       `{$listingTable}`.`id`,
       `{$listingTable}`.`title`,
       `{$listingTable}`.`update_date`,
       `{$listingTable}`.`create_date`
FROM `{$listingTable}`
WHERE `{$listingTable}`.`component_mode` = 'ebay';

INSERT INTO `{$newTable}`
SELECT `{$listingCategoryTable}`.`id`,
       `{$listingCategoryTable}`.`listing_id`,
       `{$listingCategoryTable}`.`listing_id`,
       `{$listingCategoryTable}`.`category_id`,
       IF(`{$listingTable}`.`categories_add_action` = 2, 1, `{$listingTable}`.`categories_add_action`),
       NULL,
       0,
       `{$listingTable}`.`categories_delete_action`,
       `{$listingCategoryTable}`.`update_date`,
       `{$listingCategoryTable}`.`create_date`
FROM `{$listingCategoryTable}`
INNER JOIN {$listingTable}
ON {$listingTable}.id = {$listingCategoryTable}.listing_id
WHERE `{$listingTable}`.`component_mode` = 'ebay';

SQL
);
    }

    //-----------------------------------

    private function processEbayConditionForMigration()
    {
        $ebayTemplateGeneralTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_template_general'
        );

        $select = $this->installer->getConnection()
            ->select()
            ->from($ebayTemplateGeneralTable, array('template_general_id', 'condition_mode', 'condition_value'));
        $data = $this->installer->getConnection()->fetchAll($select);

        if (empty($data)) {
            return;
        }

        $nonConvertedConditionValues = array(
            1000, 1500, 1750, 2000, 2500, 3000, 4000, 5000, 6000, 7000,
        );

        $descriptionTemplateIds = array();
        foreach ($data as $row) {
            if ((int)$row['condition_mode'] == 0 &&
                in_array((int)$row['condition_value'], $nonConvertedConditionValues)
            ) {
                continue;
            }

            if (isset($this->generalDescriptionCorrelation[(int)$row['template_general_id']])) {
                $descriptionTemplateIds = array_merge(
                    $descriptionTemplateIds,
                    $this->generalDescriptionCorrelation[(int)$row['template_general_id']]
                );
            }
        }

        if (empty($descriptionTemplateIds)) {
            return;
        }

        $templateDescriptionTable = $this->installer->getTable(
            'm2epro_ebay_template_description'
        );

        $descriptionTemplateIds = array_map('intval',$descriptionTemplateIds);

        $select = $this->installer->getConnection()
            ->select()
            ->from($templateDescriptionTable, array('id','title'))
            ->where('id IN(?)', $descriptionTemplateIds);

        $migrationData = $this->installer->getConnection()->fetchAll($select);

        if (empty($migrationData)) {
            return;
        }

        $migrationTable = $this->installer->getTable('m2epro_migration_v6');
        $migrationTableData = array(
            'component' => 'ebay',
            'group' => 'condition_values',
            'data' => json_encode($migrationData)
        );
        $this->installer->getConnection()->insert($migrationTable, $migrationTableData);
    }

    private function processEbayVariationIgnoreForMigration()
    {
        $ebayTemplateGeneralTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_template_general'
        );
        $templateGeneralTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_template_general'
        );
        $listingTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_listing'
        );

        $select = $this->installer->getConnection()
            ->select()
            ->from($ebayTemplateGeneralTable, '')
            ->join($templateGeneralTable, 'id=template_general_id', 'id')
            ->where('variation_ignore = 1');

        $generalTemplates = $this->installer->getConnection()->fetchAll($select);
        if (empty($generalTemplates)) {
            return;
        }

        $generalTemplateIds = array();
        foreach ($generalTemplates as $template) {
            $generalTemplateIds[] = $template['id'];
        }

        $select = $this->installer->getConnection()
            ->select()
            ->from($listingTable, array('id', 'title'))
            ->where('template_general_id IN (?)', $generalTemplateIds);

        $data = $this->installer->getConnection()->fetchAll($select);
        if (empty($data)) {
            return;
        }

        $migrationTable = $this->installer->getTable('m2epro_migration_v6');
        $migrationTableData = array(
            'component' => 'ebay',
            'group' => 'variation_ignore',
            'data' => json_encode($data)
        );
        $this->installer->getConnection()->insert($migrationTable, $migrationTableData);
    }

    private function processListingsForMigration()
    {
        $listingTable = $this->installer->getTable('m2epro'.self::PREFIX_TABLE_BACKUP.'_listing');

        $select = $this->installer->getConnection()
            ->select()
            ->from($listingTable, array(
                'id', 'title',
                'component_mode',
                'hide_products_others_listings',
                'synchronization_start_type',
                'synchronization_stop_type',
            ));

        $data = $this->installer->getConnection()->fetchAll($select);

        $migrationHideData = array();
        $migrationSynchTypeData = array();
        foreach ($data as $row) {
            if ((int)$row['hide_products_others_listings'] == 1) {
                $migrationHideData[(int)$row['id']] = array(
                    'title' => $row['title'],
                    'component_mode' => $row['component_mode'],
                );
            }

            if ((int)$row['synchronization_start_type'] != 1 ||
                (int)$row['synchronization_stop_type'] != 0) {

                $migrationSynchTypeData[(int)$row['id']] = array(
                    'title' => $row['title'],
                    'component_mode' => $row['component_mode'],
                );
            }
        }

        $migrationTable = $this->installer->getTable('m2epro_migration_v6');

        if (!empty($migrationHideData)) {
            $migrationTableData = array(
                'component' => '*',
                'group' => 'hide_products_others_listings',
                'data' => json_encode($migrationHideData)
            );
            $this->installer->getConnection()->insert($migrationTable, $migrationTableData);
        }

        if (!empty($migrationSynchTypeData)) {
            $migrationTableData = array(
                'component' => '*',
                'group' => 'listing_synchronization_type',
                'data' => json_encode($migrationSynchTypeData)
            );
            $this->installer->getConnection()->insert($migrationTable, $migrationTableData);
        }
    }

    private function processEbayScheduleForMigration()
    {
        $ebayTemplateSynchronizationTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_template_synchronization'
        );
        $templateSynchronizationTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_template_synchronization'
        );

        $select = $this->installer->getConnection()
            ->select()
            ->from($ebayTemplateSynchronizationTable, 'relist_schedule_type')
            ->joinLeft(
                $templateSynchronizationTable,
                'id=template_synchronization_id',
                array('id', 'title')
            )->where('relist_schedule_type IN(1,2)');

        $data = $this->installer->getConnection()->fetchAll($select);

        if (empty($data)) {
            return;
        }

        $migrationDelayData = array();
        $migrationListData = array();
        foreach ($data as $row) {
            if ((int)$row['relist_schedule_type'] == 1) {
                $migrationDelayData[] = array(
                    'id' => $row['id'],
                    'title' => $row['title'],
                );
            }

            if ((int)$row['relist_schedule_type'] == 2) {
                $migrationListData[] = array(
                    'id' => $row['id'],
                    'title' => $row['title'],
                );
            }
        }

        $migrationTable = $this->installer->getTable('m2epro_migration_v6');

        if (!empty($migrationListData)) {
            $migrationTableData = array(
                'component' => 'ebay',
                'group' => 'schedule_list',
                'data' => json_encode($migrationListData)
            );
            $this->installer->getConnection()->insert($migrationTable, $migrationTableData);
        }

        if (!empty($migrationDelayData)) {
            $migrationTableData = array(
                'component' => 'ebay',
                'group' => 'schedule_delay_after_end',
                'data' => json_encode($migrationDelayData)
            );
            $this->installer->getConnection()->insert($migrationTable, $migrationTableData);
        }
    }

    private function processCommonScheduleForMigration()
    {
        $templateSynchronizationTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_template_synchronization'
        );
        $amazonTemplateSynchronizationTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_amazon_template_synchronization'
        );
        $buyTemplateSynchronizationTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_buy_template_synchronization'
        );
        $playTemplateSynchronizationTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_play_template_synchronization'
        );

        $migrationTable = $this->installer->getTable('m2epro_migration_v6');

        $select = $this->installer->getConnection()
            ->select()
            ->from($templateSynchronizationTable, array('id', 'title', 'component_mode'))
            ->joinRight($amazonTemplateSynchronizationTable, 'id=template_synchronization_id', '')
            ->where('relist_schedule_type != 0');

        $data = $this->installer->getConnection()->fetchAll($select);

        $migrationData = array();
        if (!empty($data)) {
            $migrationData = array_merge($migrationData, $data);
        }

        $select = $this->installer->getConnection()
            ->select()
            ->from($templateSynchronizationTable, array('id', 'title', 'component_mode'))
            ->joinRight($buyTemplateSynchronizationTable, 'id=template_synchronization_id', '')
            ->where('relist_schedule_type != 0');

        $data = $this->installer->getConnection()->fetchAll($select);

        if (!empty($data)) {
            $migrationData = array_merge($migrationData, $data);
        }

        $select = $this->installer->getConnection()
            ->select()
            ->from($templateSynchronizationTable, array('id', 'title', 'component_mode'))
            ->joinRight($playTemplateSynchronizationTable, 'id=template_synchronization_id', '')
            ->where('relist_schedule_type != 0');

        $data = $this->installer->getConnection()->fetchAll($select);

        if (!empty($data)) {
            $migrationData = array_merge($migrationData, $data);
        }

        if (empty($migrationData)) {
            return;
        }

        $migrationTableData = array(
            'component' => '*',
            'group' => 'relist_schedule',
            'data' => json_encode($migrationData)
        );
        $this->installer->getConnection()->insert($migrationTable, $migrationTableData);
    }

    private function processProductDetailsForMigration()
    {
        $ebayTemplateGeneralTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_template_general'
        );

        $select = $this->installer->getConnection()
            ->select()
            ->from($ebayTemplateGeneralTable, array('template_general_id', 'product_details'));

        $data = $this->installer->getConnection()->fetchAll($select);

        if (empty($data)) {
            return;
        }

        $descriptionTemplateIds = array();
        foreach ($data as $row) {
            if (empty($row['product_details']) || !is_string($row['product_details'])) {
                continue;
            }

            $productDetails = (array)json_decode($row['product_details'], true);
            if (empty($productDetails)) {
                continue;
            }

            foreach ($productDetails as $key => $value) {
                if (strpos($key, '_mode') === false || (int)$value != 1) {
                    continue;
                }

                if (isset($this->generalDescriptionCorrelation[(int)$row['template_general_id']])) {
                    $descriptionTemplateIds = array_merge(
                        $descriptionTemplateIds,
                        $this->generalDescriptionCorrelation[(int)$row['template_general_id']]
                    );
                }

                break;
            }
        }

        if (empty($descriptionTemplateIds)) {
            return;
        }

        $descriptionTemplateIds = array_map('intval',$descriptionTemplateIds);

        $templateDescriptionTable = $this->installer->getTable(
            'm2epro_ebay_template_description'
        );

        $select = $this->installer->getConnection()
            ->select()
            ->from($templateDescriptionTable, array('id', 'title'))
            ->where('id IN(?)',$descriptionTemplateIds);
        $migrationData = $this->installer->getConnection()->fetchAll($select);

        if (empty($migrationData)) {
            return;
        }

        $migrationTable = $this->installer->getTable('m2epro_migration_v6');
        $migrationTableData = array(
            'component' => 'ebay',
            'group' => 'product_details',
            'data' => json_encode($migrationData)
        );
        $this->installer->getConnection()->insert($migrationTable, $migrationTableData);
    }

    private function processEbayUnusedDescriptionTemplatesForMigration()
    {
        $templateDescriptionTable = $this->installer->getTable(
            'm2epro_ebay_template_description'
        );

        if (empty($this->unusedTemplatesDescriptionIds)) {
            return;
        }

        $descriptionTemplateIds = array_map('intval', $this->unusedTemplatesDescriptionIds);

        $select = $this->installer->getConnection()
            ->select()
            ->from($templateDescriptionTable, array('id','title'))
            ->where('id IN(?)', $descriptionTemplateIds);
        $migrationData = $this->installer->getConnection()->fetchAll($select);

        $migrationTable = $this->installer->getTable('m2epro_migration_v6');
        $migrationTableData = array(
            'component' => 'ebay',
            'group' => 'unused_description_templates',
            'data' => json_encode($migrationData)
        );
        $this->installer->getConnection()->insert($migrationTable, $migrationTableData);
    }

    //-----------------------------------

    private function createEbayTemplatePolicy()
    {
        $newTable = $this->installer->getTable('m2epro_ebay_template_policy');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  api_name VARCHAR(255) NOT NULL,
  api_identifier VARCHAR(255) NOT NULL,
  PRIMARY KEY (id)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);
    }

    private function createEbayListingAutoFilter()
    {
        $newTable = $this->installer->getTable('m2epro_ebay_listing_auto_filter');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  listing_id INT(11) UNSIGNED NOT NULL,
  rule_data TEXT NOT NULL,
  adding_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  adding_template_category_id INT(11) UNSIGNED DEFAULT NULL,
  adding_duplicate TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  deleting_mode TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  update_date DATETIME DEFAULT NULL,
  create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  INDEX adding_template_category_id (adding_template_category_id),
  INDEX listing_id (listing_id),
  INDEX title (title)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);
    }

    private function createEbayDictionaryPolicyTable()
    {
        $newTable = $this->installer->getTable('m2epro_ebay_dictionary_policy');

        $this->installer->getConnection()->multi_query(<<<SQL

DROP TABLE IF EXISTS {$newTable};
CREATE TABLE {$newTable} (
  id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  account_id INT(11) UNSIGNED NOT NULL,
  type TINYINT(2) UNSIGNED NOT NULL,
  api_name VARCHAR(255) NOT NULL,
  api_identifier VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  INDEX account_id (account_id),
  INDEX api_identifier (api_identifier),
  INDEX api_name (api_name),
  INDEX type (type)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
);
    }

    //####################################

    private function getCategoryPathById($categoryId, $delimiter = ' -> ')
    {
        $titles = array();

        for ($i = 1; $i < 8; $i++) {

            $dictionaryTable = $this->installer->getTable('m2epro_ebay_dictionary_category');

            $category = $this->installer->getConnection()
                 ->select()->from($dictionaryTable,'*')
                 ->where('category_id = ?', $categoryId)
                 ->query()->fetch();

            if (empty($category) || ($i == 1 && !$category['is_leaf'])) {
                return '';
            }

            $titles[] = $category['title'];

            if (!$category['parent_id']) {
                break;
            }

            $categoryId = (int)$category['parent_id'];
        }

        return implode($delimiter, array_reverse($titles));
    }

    private function getStoreCategoryPathById($categoryId, $accountId, $delimiter = ' -> ')
    {
        if (empty($categoryId) || empty($accountId)) {
            return '';
        }

        $ebayStoreCategoryTable = $this->installer->getTable(
            'm2epro'.self::PREFIX_TABLE_BACKUP.'_ebay_account_store_category'
        );

        $categories = $this->installer->getConnection()
            ->select()->from($ebayStoreCategoryTable,'*')
            ->where('account_id = ?', $accountId)
            ->where('category_id = ?', $categoryId)
            ->query()
            ->fetchAll();

        $path = array();

        while (true) {
            $currentCategory = NULL;
            foreach ($categories as $category) {
                if ($category['category_id'] == $categoryId) {
                    $currentCategory = $category;
                    break;
                }
            }

            if (is_null($currentCategory)) {
                break;
            }

            $path[] = $currentCategory['title'];

            if ($currentCategory['parent_id'] == 0) {
                break;
            }

            $categoryId = $currentCategory['parent_id'];
        }

        return implode($delimiter, array_reverse($path));
    }

    //####################################

    private function convertProductDetails($oldProductDetails)
    {
        $newProductDetails = array(
            'ean' => '',
            'upc' => '',
            'isbn' => '',
            'epid' => '',
        );
        $oldProductDetails = json_decode($oldProductDetails, true);

        if (isset($oldProductDetails['product_details_isbn_mode']) &&
            $oldProductDetails['product_details_isbn_mode'] == 2 &&
            isset($oldProductDetails['product_details_isbn_ca'])) {
            $newProductDetails['isbn'] = $oldProductDetails['product_details_isbn_ca'];
        }
        if (isset($oldProductDetails['product_details_epid_mode']) &&
            $oldProductDetails['product_details_epid_mode'] == 2 &&
            isset($oldProductDetails['product_details_epid_ca'])) {
            $newProductDetails['epid'] = $oldProductDetails['product_details_epid_ca'];
        }
        if (isset($oldProductDetails['product_details_ean_mode']) &&
            $oldProductDetails['product_details_ean_mode'] == 2 &&
            isset($oldProductDetails['product_details_ean_ca'])) {
            $newProductDetails['ean'] = $oldProductDetails['product_details_ean_ca'];
        }
        if (isset($oldProductDetails['product_details_upc_mode']) &&
            $oldProductDetails['product_details_upc_mode'] == 2 &&
            isset($oldProductDetails['product_details_upc_ca'])) {
            $newProductDetails['upc'] = $oldProductDetails['product_details_upc_ca'];
        }

        return json_encode($newProductDetails);
    }

    //####################################
}