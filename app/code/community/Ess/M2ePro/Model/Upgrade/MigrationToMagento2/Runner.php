<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_MigrationToMagento2_Runner
{
    const MAP_PREFIX        = 'mtm2map';
    const CONFIG_PREFIX     = '/migrationtomagento2/source/';

    const MAGENTO_PRODUCTS_TABLE_NAME   = 'magento_products';
    const MAGENTO_ORDERS_TABLE_NAME     = 'magento_orders';
    const MAGENTO_STORES_TABLE_NAME     = 'magento_stores';
    const MAGENTO_CATEGORIES_TABLE_NAME = 'magento_categories';

    //########################################

    public function initialize()
    {
        $config = Mage::helper('M2ePro/Module')->getConfig();

        $config->setGroupValue(
            self::CONFIG_PREFIX, 'is_prepared_for_migration', 0
        );
        $config->setGroupValue(
            self::CONFIG_PREFIX. 'm2epro/', 'version', Mage::helper('M2ePro/Module')->getPublicVersion()
        );
    }

    public function run()
    {
        $this->removeOldTables();
        $this->clearUnnecessaryData();
        $this->createMagentoMap();
    }

    public function complete()
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(self::CONFIG_PREFIX, 'is_prepared_for_migration', 1);
        Mage::helper('M2ePro/Module_Maintenance')->enable();
        Mage::helper('M2ePro/Magento')->clearCache();
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getMappingTablesRecordsCount()
    {
        /** @var Varien_Db_Adapter_Interface $connection */
        $connection = Mage::getSingleton('core/resource')->getConnection('core_setup');

        $mappingTables = array(
            self::MAGENTO_PRODUCTS_TABLE_NAME,
            self::MAGENTO_ORDERS_TABLE_NAME,
            self::MAGENTO_STORES_TABLE_NAME,
            self::MAGENTO_CATEGORIES_TABLE_NAME
        );

        $tablesInfo = array();

        foreach ($mappingTables as $mappingTable) {

            $mappingTableName = $this->getMapTableName($mappingTable);
            if (!$connection->isTableExists($mappingTableName)){
                throw new Ess_M2ePro_Model_Exception_Logic("Mapping table {$mappingTableName} does not exist.");
            }

            $countSelect = $connection->select()->from($mappingTableName, new \Zend_Db_Expr('COUNT(*)'));

            $tablesInfo[$mappingTable] = (int)$connection->fetchOne($countSelect);
        }

        return $tablesInfo;
    }

    /**
     * @param $mappingTableName
     * @param $limit
     * @param $offset
     * @return array
     * @throws Exception
     */
    public function selectDataFromMappingTable($mappingTableName, $limit, $offset)
    {
        if (!$this->isMappingTableNameValid($mappingTableName)) {
            throw new \InvalidArgumentException('Invalid mapping table name.');
        }

        /** @var Varien_Db_Adapter_Interface $connection */
        $connection = Mage::getSingleton('core/resource')->getConnection('core_setup');

        $select = $connection->select()
            ->from($this->getMapTableName($mappingTableName))
            ->order('id ASC')
            ->limit($limit, $offset);

        return $connection->fetchAll($select);
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

    protected function clearUnnecessaryData()
    {
        $resource   = Mage::getSingleton('core/resource');
        /** @var Varien_Db_Adapter_Interface $connection */
        $connection = $resource->getConnection('core_write');

        $tablesForClearing = array(
            'm2epro_system_log',
            'm2epro_operation_history'
        );

        foreach ($tablesForClearing as $tableName) {
            $connection->truncateTable(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix($tableName)
            );
        }
    }

    public function createMagentoMap()
    {
        /** @var Varien_Db_Adapter_Interface $connection */
        $connection = Mage::getSingleton('core/resource')->getConnection('core_setup');

        // ---------------------------------------
        $tableName = $this->getMapTableName(self::MAGENTO_PRODUCTS_TABLE_NAME);
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
        $tableName = $this->getMapTableName(self::MAGENTO_ORDERS_TABLE_NAME);
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
        $tableName = $this->getMapTableName(self::MAGENTO_STORES_TABLE_NAME);
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
        $tableName = $this->getMapTableName(self::MAGENTO_CATEGORIES_TABLE_NAME);
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

    /**
     * @param $tableName
     * @return bool
     */
    protected function isMappingTableNameValid($tableName)
    {
        return in_array(
            $tableName,
            array(
                self::MAGENTO_PRODUCTS_TABLE_NAME,
                self::MAGENTO_ORDERS_TABLE_NAME,
                self::MAGENTO_STORES_TABLE_NAME,
                self::MAGENTO_CATEGORIES_TABLE_NAME
            )
        );
    }

    //########################################
}
