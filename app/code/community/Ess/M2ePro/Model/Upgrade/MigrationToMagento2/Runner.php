<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_MigrationToMagento2_Runner
{
    const BACKUP_PREFIX     = 'mtm2';
    const MAP_PREFIX        = 'mtm2map';
    const CONFIG_PREFIX     = '/migrationtomagento2/source/';

    //########################################

    public function initialize()
    {
        $config = Mage::helper('M2ePro/Module')->getConfig();

        $config->setGroupValue(
            self::CONFIG_PREFIX, 'is_prepared_for_migration', 0
        );
        $config->setGroupValue(
            self::CONFIG_PREFIX, 'version', Mage::helper('M2ePro/Module')->getPublicVersion()
        );
        $config->setGroupValue(
            self::CONFIG_PREFIX. 'magento/', 'version', Mage::helper('M2ePro/Magento')->getVersion()
        );
        $config->setGroupValue(
            self::CONFIG_PREFIX. 'magento/', 'edition', Mage::helper('M2ePro/Magento')->getEditionName()
        );
        $config->setGroupValue(
            self::CONFIG_PREFIX. 'magento/', 'tables_prefix', Mage::helper('M2ePro/Magento')->getDatabaseTablesPrefix()
        );
        $config->setGroupValue(
            self::CONFIG_PREFIX. 'm2epro/', 'version', Mage::helper('M2ePro/Module')->getPublicVersion()
        );
    }

    public function run()
    {
        $this->removeOldTables();
        $this->backupTables();
        $this->clearUnnecessaryData();
        $this->createMagentoMap();
    }

    public function complete()
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(self::CONFIG_PREFIX, 'is_prepared_for_migration', 1);
        Mage::helper('M2ePro/Module_Maintenance')->enable();
        Mage::helper('M2ePro/Magento')->clearCache();
    }

    //########################################

    protected function removeOldTables()
    {
        $resource   = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_setup');

        $tablesForRemove = array(
            Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_setup'),
        );

        $tablesPrefixesForRemove = array(
            'm2epro__backup_v5',
            'ess__backup_v5',
            'm2epro__source',
            'ess__source',
            'm2epro__backup_v611',
            'm2epro__backup_v630',
            'm2epro_bv630',
            'm2epro__b_6'
        );

        foreach ($tablesPrefixesForRemove as $prefix) {
            $prefix = str_replace('_', '\_', $prefix);
            $prefixedTables = $connection->query('SHOW TABLES LIKE \'%'.$prefix.'%\'')->fetchAll(Zend_Db::FETCH_COLUMN);
            $tablesForRemove = array_merge($tablesForRemove, $prefixedTables);
        }

        foreach ($tablesForRemove as $tableName) {
            $connection->dropTable($tableName);
        }
    }

    protected function backupTables()
    {
        $tablesForBackup = array(
            'listing_log',
            'system_log',
            'order_log',
            'synchronization_log',
            'ebay_account_pickup_store_log',

            'amazon_order',
            'amazon_order_item',
            'ebay_order',
            'ebay_order_item',
            'ebay_order_external_transaction',
            'order',
            'order_item',
            'walmart_order',
            'walmart_order_item',

            'listing_product_instruction',
            'listing_product_scheduled_action',

            'lock_item',
            'lock_transactional',

            'processing',
            'processing_lock',
            'request_pending_single',
            'request_pending_partial',
            'request_pending_partial_data',
            'connector_command_pending_processing_single',
            'connector_command_pending_processing_partial',
            'ebay_listing_product_action_processing',
            'amazon_listing_product_action_processing',
            'amazon_listing_product_action_processing_list_sku',
            'amazon_order_action_processing',
            'walmart_listing_product_action_processing',
            'walmart_listing_product_action_processing_list',

            'operation_history',
            'magento_product_websites_update',
            'order_change',
            'stop_queue',
            'archived_entity'
        );

        $installer = new Ess_M2ePro_Model_Upgrade_MySqlSetup('M2ePro_setup');
        $installer->versionFrom = '';
        $installer->versionTo = self::BACKUP_PREFIX;

        /** @var Ess_M2ePro_Model_Upgrade_Backup $backupObject */
        $backupObject = Mage::getModel(
            'M2ePro/Upgrade_Backup', array(
                                       $installer, $tablesForBackup
                                   )
        );

        $backupObject->create();
    }

    protected function clearUnnecessaryData()
    {
        $resource   = Mage::getSingleton('core/resource');
        /** @var Varien_Db_Adapter_Interface $connection */
        $connection = $resource->getConnection('core_write');

        $tablesForClearing = array(
            'm2epro_ebay_dictionary_category',
            'm2epro_ebay_dictionary_marketplace',
            'm2epro_ebay_dictionary_shipping',
            'm2epro_ebay_dictionary_motor_epid',
            'm2epro_ebay_dictionary_motor_ktype',

            'm2epro_amazon_dictionary_category',
            'm2epro_amazon_dictionary_category_product_data',
            'm2epro_amazon_dictionary_marketplace',
            'm2epro_amazon_dictionary_specific',

            'm2epro_walmart_dictionary_category',
            'm2epro_walmart_dictionary_marketplace',
            'm2epro_walmart_dictionary_specific',

            'm2epro_listing_log',
            'm2epro_system_log',
            'm2epro_synchronization_log',
            'm2epro_ebay_account_pickup_store_log',

            'm2epro_listing_product_instruction',
            'm2epro_listing_product_scheduled_action',

            'm2epro_lock_item',
            'm2epro_lock_transactional',

            'm2epro_processing',
            'm2epro_processing_lock',
            'm2epro_request_pending_single',
            'm2epro_request_pending_partial',
            'm2epro_request_pending_partial_data',
            'm2epro_connector_command_pending_processing_single',
            'm2epro_connector_command_pending_processing_partial',
            'm2epro_ebay_listing_product_action_processing',
            'm2epro_amazon_listing_product_action_processing',
            'm2epro_amazon_listing_product_action_processing_list_sku',
            'm2epro_amazon_order_action_processing',
            'm2epro_walmart_listing_product_action_processing',
            'm2epro_walmart_listing_product_action_processing_list',

            'm2epro_operation_history',
            'm2epro_magento_product_websites_update',
            'm2epro_order_change',
            'm2epro_stop_queue',
            'm2epro_archived_entity'
        );

        foreach ($tablesForClearing as $tableName) {
            $connection->truncateTable(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix($tableName)
            );
        }

        //clear all orders data older than one month
        $minData = new DateTime('now', new DateTimeZone('UTC'));
        $minData->modify('-1 month');

        $connection->delete(
            Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_order'),
            'create_date < \'' . $minData->format('Y-m-d H:i:s') . '\''
        );

        $orderItemTable = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_order_item');
        $orderTable = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_order');

        $select = $connection->select()
            ->from($orderItemTable)
            ->joinLeft(
                $orderTable,
                "{$orderItemTable}.order_id={$orderTable}.id"
            )
            ->where("{$orderTable}.id IS NULL");
        $connection->query(
            $connection->deleteFromSelect($select, $orderItemTable)
        );

        $orderLogTable = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_order_log');

        $select = $connection->select()
            ->from($orderLogTable)
            ->joinLeft(
                $orderTable,
                "{$orderLogTable}.order_id={$orderTable}.id"
            )
            ->where("{$orderTable}.id IS NULL");
        $connection->query(
            $connection->deleteFromSelect($select, $orderLogTable)
        );

        $orderExternalTransactionTable = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_order_external_transaction');

        $select = $connection->select()
            ->from($orderExternalTransactionTable)
            ->joinLeft(
                $orderTable,
                "{$orderExternalTransactionTable}.order_id={$orderTable}.id"
            )
            ->where("{$orderTable}.id IS NULL");
        $connection->query(
            $connection->deleteFromSelect(
                $select, Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix('m2epro_ebay_order_external_transaction')
            )
        );

        $components = array(
            Ess_M2ePro_Helper_Component_Amazon::NICK,
            Ess_M2ePro_Helper_Component_Ebay::NICK,
            Ess_M2ePro_Helper_Component_Walmart::NICK
        );

        foreach ($components as $component) {
            $componentTable = Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix('m2epro_'.$component.'_order');
            $select = $connection->select()
                ->from($componentTable)
                ->joinLeft(
                    $orderTable,
                    "{$componentTable}.order_id={$orderTable}.id"
                )
                ->where("{$orderTable}.id IS NULL");
            $connection->query(
                $connection->deleteFromSelect($select, $componentTable)
            );

            $componentTable = Mage::helper('M2ePro/Module_Database_Structure')
                ->getTableNameWithPrefix('m2epro_'.$component.'_order_item');
            $select = $connection->select()
                ->from($componentTable)
                ->joinLeft(
                    $orderItemTable,
                    "{$componentTable}.order_item_id={$orderItemTable}.id"
                )
                ->where("{$orderItemTable}.id IS NULL");
            $connection->query(
                $connection->deleteFromSelect($select, $componentTable)
            );
        }
    }

    protected function createMagentoMap()
    {
        /** @var Varien_Db_Adapter_Interface $connection */
        $connection = Mage::getSingleton('core/resource')->getConnection('core_setup');

        // ---------------------------------------
        $tableName = $this->getMapTableName('magento_products');
        if($connection->isTableExists($tableName)){
            $connection->dropTable($tableName);
        }

        $table = $connection->newTable($tableName)
            ->addColumn(
                'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'  => true,
                    )
            )
            ->addColumn(
                'product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                                'unsigned' => true,
                                'nullable' => false,
                                'default'  => '0',
                            )
            )
            ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_TEXT, 255);
        $connection->createTable($table);

        $tablesWithRelations = array(
            'm2epro_listing_product',
            'm2epro_listing_other',
            'm2epro_listing_product_variation_option',
            'm2epro_amazon_item',
            'm2epro_ebay_item',
            'm2epro_walmart_item',
            'm2epro_order_item',
            'm2epro_order_matching'
        );

        foreach ($tablesWithRelations as $tableWithRelation) {
            $select = $connection->select()
                ->from(
                    array(
                        'main_table' => Mage::helper('M2ePro/Module_Database_Structure')
                            ->getTableNameWithPrefix($tableWithRelation)),
                    'product_id'
                )
                ->joinInner(
                    array(
                        'cp' => Mage::helper('M2ePro/Module_Database_Structure')
                            ->getTableNameWithPrefix('catalog/product')),
                    'main_table.product_id=cp.entity_id',
                    'sku'
                )
                ->joinLeft(array('t' => $tableName), 't.product_id=cp.entity_id', '')
                ->where('t.product_id IS NULL')
                ->group('main_table.product_id');

            $connection->query(
                $connection->insertFromSelect($select, $tableName, array('product_id', 'sku'))
            );
        }

        // ---------------------------------------

        // ---------------------------------------
        $tableName = $this->getMapTableName('magento_orders');
        if($connection->isTableExists($tableName)){
            $connection->dropTable($tableName);
        }

        $table = $connection->newTable($tableName)
            ->addColumn(
                'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'  => true,
                    )
            )
            ->addColumn(
                'order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                              'unsigned' => true,
                              'nullable' => false,
                              'default'  => '0',
                          )
            )
            ->addColumn('magento_order_num', Varien_Db_Ddl_Table::TYPE_TEXT, 255);
        $connection->createTable($table);

        $select = $connection->select()
            ->from(
                array(
                    'o' => Mage::helper('M2ePro/Module_Database_Structure')
                        ->getTableNameWithPrefix('m2epro_order')),
                array('order_id' => 'magento_order_id')
            )
            ->joinInner(
                array(
                    'so' => Mage::helper('M2ePro/Module_Database_Structure')
                        ->getTableNameWithPrefix('sales/order')),
                'o.magento_order_id = so.entity_id',
                array('magento_order_num' => 'increment_id')
            )
            ->joinLeft(array('t' => $tableName), 't.order_id=so.entity_id', '')
            ->where('t.order_id IS NULL')
            ->group('o.magento_order_id');

        $connection->query(
            $connection->insertFromSelect(
                $select, $tableName, array('order_id', 'magento_order_num'), Varien_Db_Adapter_Interface::INSERT_IGNORE
            )
        );
        // ---------------------------------------

        // ---------------------------------------
        $tableName = $this->getMapTableName('magento_stores');
        if($connection->isTableExists($tableName)){
            $connection->dropTable($tableName);
        }

        $table = $connection->newTable($tableName)
            ->addColumn(
                'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'  => true,
                    )
            )
            ->addColumn(
                'store_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                              'unsigned' => true,
                              'nullable' => false,
                              'default'  => '0',
                          )
            )
            ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255)
            ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255);
        $connection->createTable($table);

        $tablesWithRelations = array(
            'm2epro_listing',
            'm2epro_order',
            'm2epro_amazon_item',
            'm2epro_ebay_item',
            'm2epro_walmart_item',
        );

        foreach ($tablesWithRelations as $tableWithRelation) {
            $select = $connection->select()
                ->from(
                    array(
                        'main_table' => Mage::helper('M2ePro/Module_Database_Structure')
                            ->getTableNameWithPrefix($tableWithRelation)),
                    'store_id'
                )
                ->joinInner(
                    array(
                        'cs' => Mage::helper('M2ePro/Module_Database_Structure')
                            ->getTableNameWithPrefix('core/store')),
                    'main_table.store_id=cs.store_id',
                    array('name', 'code')
                )
                ->joinLeft(array('t' => $tableName), 't.store_id=cs.store_id', '')
                ->where('t.store_id IS NULL')
                ->group('main_table.store_id');

            $connection->query(
                $connection->insertFromSelect(
                    $select, $tableName, array('store_id', 'name', 'code'), Varien_Db_Adapter_Interface::INSERT_IGNORE
                )
            );
        }

        // ---------------------------------------

        // ---------------------------------------
        $tableName = $this->getMapTableName('magento_categories');
        if($connection->isTableExists($tableName)){
            $connection->dropTable($tableName);
        }

        $table = $connection->newTable($tableName)
            ->addColumn(
                'id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'  => true,
                    )
            )
            ->addColumn(
                'category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
                                 'unsigned' => true,
                                 'nullable' => false,
                                 'default'  => '0',
                             )
            )
            ->addColumn('category_path', Varien_Db_Ddl_Table::TYPE_TEXT);
        $connection->createTable($table);

        /** @var Ess_M2ePro_Model_Resource_Listing_Auto_Category_Collection $autoCategoryCollection */
        $autoCategoryCollection = Mage::getResourceModel('M2ePro/Listing_Auto_Category_Collection');
        $categoriesIds = $autoCategoryCollection->getColumnValues('category_id');

        if (empty($categoriesIds)) {
            return;
        }

        /** @var Mage_Catalog_Model_Resource_Category_Collection $collection */
        $collection = Mage::getResourceModel('catalog/category_collection');
        $collection->addIdFilter($categoriesIds);

        $insertData = array();

        foreach ($collection as $category) {
            /** @var Mage_Catalog_Model_Category $category */

            $path        = array();
            $pathInStore = $category->getPathInStore();
            $pathIds     = array_reverse(explode(',', $pathInStore));

            $categories = $category->getParentCategories();

            foreach ($pathIds as $categoryId) {
                if (isset($categories[$categoryId]) && $categories[$categoryId]->getName()) {
                    $path[] = $categories[$categoryId]->getName();
                }
            }

            $insertData[$category->getEntityId()] = array(
                'category_id' => $category->getEntityId(),
                'category_path' => implode('/', $path)
            );
        }

        $connection->insertMultiple($tableName, $insertData);
        // ---------------------------------------
    }

    //########################################

    protected function getMapTableName($name)
    {
        return Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro__' . self::MAP_PREFIX . '_' . $name);
    }

    //########################################
}
