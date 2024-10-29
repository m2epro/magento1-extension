<?php

class Ess_M2ePro_Sql_Update_y24_m09_AddWalmartProductTypes
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    const LONG_COLUMN_SIZE = 16777217;
    const WIZARD_STATUS_ACTIVE = 1;
    const WIZARD_STATUS_SKIPPED = 3;

    public function execute()
    {
        $this->addProductTypeToListingProduct();
        $this->updateAutoActions();
        $this->dropDictionarySpecific();
        $this->updateMarketplaceDictionary();
        $this->updateCategoryDictionary();
        $this->createDictionaryProductType();
        $this->createProductType();
        $this->insertIntoWizard();
        $this->deleteAttributesFromDescriptionPolicy();
        $this->deleteAttributesFromSellingPolicy();
    }

    private function addProductTypeToListingProduct()
    {
        $tableModifier = $this->_installer->getTableModifier('walmart_listing_product');
        $tableModifier->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_Listing_Product::COLUMN_PRODUCT_TYPE_ID,
            'INT UNSIGNED',
            'NULL'
        );
    }

    private function updateAutoActions()
    {
        $walmartListingModifier = $this->_installer->getTableModifier('walmart_listing');
        $listingAutoCategoryGroupModifier = $this->_installer->getTableModifier('walmart_listing_auto_category_group');

        if (
            !$walmartListingModifier->isColumnExists('auto_global_adding_category_template_id')
            || !$walmartListingModifier->isColumnExists('auto_website_adding_category_template_id')
            || !$listingAutoCategoryGroupModifier->isColumnExists('adding_category_template_id')
        ) {
            return;
        }

        $this->resetListingAutoActionsByCategoryTemplate();
        $this->resetListingAutoActionsByCategoryGroup();

        $this->removeAutoActionsWithCategoryTemplateId();
        $this->changeSchemeAutoActions();
    }

    private function resetListingAutoActionsByCategoryTemplate()
    {
        $subSelect = $this->_installer->getConnection()
                          ->select()
                          ->from(
                              $this->_installer->getFullTableName('walmart_listing'),
                              'listing_id'
                          )
                          ->where(
                              'auto_global_adding_category_template_id IS NOT NULL'
                              . ' OR auto_website_adding_category_template_id IS NOT NULL'
                          );

        $this->resetListingAutoActions($subSelect);
    }

    private function resetListingAutoActionsByCategoryGroup()
    {
        $subSelect = $this->_installer->getConnection()
                                      ->select()
                                      ->from(
                                          $this->_installer->getFullTableName('listing_auto_category_group')
                                      )
                                      ->join(
                                          array(
                                              'wg' => $this->_installer->getFullTableName(
                                                  'walmart_listing_auto_category_group'
                                              )
                                          ),
                                          'id = listing_auto_category_group_id'
            )->where('wg.adding_category_template_id IS NOT NULL')
                                      ->reset('columns')
                                      ->columns('listing_id');

        $this->resetListingAutoActions($subSelect);
    }

    private function resetListingAutoActions(Varien_Db_Select $subSelect)
    {
        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName('listing'),
            array(
                'auto_mode' => 0,
                'auto_global_adding_mode' => 0,
                'auto_global_adding_add_not_visible' => 1,
                'auto_website_adding_mode' => 0,
                'auto_website_adding_add_not_visible' => 1,
                'auto_website_deleting_mode' => 0,
            ),
            array('id IN (?)' => $subSelect)
        );
    }

    private function removeAutoActionsWithCategoryTemplateId()
    {
        $walmartGroupTableName = $this->_installer->getFullTableName('walmart_listing_auto_category_group');
        $groupTableName = $this->_installer->getFullTableName('listing_auto_category_group');
        $categoryTableName = $this->_installer->getFullTableName('listing_auto_category');

        $subSelect = "SELECT listing_auto_category_group_id FROM $walmartGroupTableName"
            . " WHERE adding_category_template_id IS NOT NULL";
        $rmFromGroups = "DELETE FROM $groupTableName WHERE id IN ($subSelect)";
        $rmFromCategory = "DELETE FROM $categoryTableName WHERE group_id IN ($subSelect)";

        $rmFromWalmartGroups = "DELETE FROM $walmartGroupTableName WHERE adding_category_template_id IS NOT NULL";

        foreach (
            array(
                $rmFromGroups,
                $rmFromCategory,
                $rmFromWalmartGroups,
            ) as $sql
        ) {
            $this->_installer->getConnection()
                ->query($sql)
                ->execute();
        }
    }

    private function changeSchemeAutoActions()
    {
        $walmartListingModifier = $this->_installer->getTableModifier('walmart_listing');
        $walmartListingModifier->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_Listing::COLUMN_AUTO_GLOBAL_ADDING_PRODUCT_TYPE_ID,
            'INT UNSIGNED',
            'NULL',
            'auto_website_adding_category_template_id'
        );
        $walmartListingModifier->addIndex(
            Ess_M2ePro_Model_Resource_Walmart_Listing::COLUMN_AUTO_GLOBAL_ADDING_PRODUCT_TYPE_ID
        );
        $walmartListingModifier->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_Listing::COLUMN_AUTO_WEBSITE_ADDING_PRODUCT_TYPE_ID,
            'INT UNSIGNED',
            'NULL',
            Ess_M2ePro_Model_Resource_Walmart_Listing::COLUMN_AUTO_GLOBAL_ADDING_PRODUCT_TYPE_ID
        );
        $walmartListingModifier->addIndex(
            Ess_M2ePro_Model_Resource_Walmart_Listing::COLUMN_AUTO_WEBSITE_ADDING_PRODUCT_TYPE_ID
        );
        $walmartListingModifier->dropColumn('auto_global_adding_category_template_id');
        $walmartListingModifier->dropIndex('auto_global_adding_category_template_id');
        $walmartListingModifier->dropColumn('auto_website_adding_category_template_id');
        $walmartListingModifier->dropIndex('auto_website_adding_category_template_id');

        $listingAutoCategoryGroupModifier = $this->_installer->getTableModifier('walmart_listing_auto_category_group');
        $listingAutoCategoryGroupModifier->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_Listing_Auto_Category_Group::COLUMN_ADDING_PRODUCT_TYPE_ID,
            'INT UNSIGNED',
            'NULL'
        );
        $listingAutoCategoryGroupModifier->addIndex(
            Ess_M2ePro_Model_Resource_Walmart_Listing_Auto_Category_Group::COLUMN_ADDING_PRODUCT_TYPE_ID
        );
        $listingAutoCategoryGroupModifier->dropColumn('adding_category_template_id');
        $listingAutoCategoryGroupModifier->dropIndex('adding_category_template_id');
    }

    private function dropDictionarySpecific()
    {
        $this->_installer->getConnection()->dropTable(
            $this->_installer->getFullTableName('walmart_dictionary_specific')
        );
    }

    private function updateMarketplaceDictionary()
    {
        $tableModifier = $this->_installer->getTableModifier('walmart_dictionary_marketplace');
        $tableModifier->truncate();
        $tableModifier->dropColumn('product_data');
        $tableModifier->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_Dictionary_Marketplace::COLUMN_PRODUCT_TYPES,
            'LONGTEXT',
            'NULL',
            Ess_M2ePro_Model_Resource_Walmart_Dictionary_Marketplace::COLUMN_SERVER_DETAILS_LAST_UPDATE_DATE,
            false,
            false
        )->commit();
    }

    private function updateCategoryDictionary()
    {
        $tableModifier = $this->_installer->getTableModifier('walmart_dictionary_category');
        $tableModifier->truncate();
        $tableModifier->dropColumn('browsenode_id');
        $tableModifier->dropIndex('browsenode_id');
        $tableModifier->dropColumn('product_data_nicks');
        $tableModifier->dropColumn('path');
        $tableModifier->dropIndex('path');
        $tableModifier->dropColumn('keywords');

        $tableModifier->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_Dictionary_Category::COLUMN_PRODUCT_TYPE_NICK,
            'VARCHAR(255)',
            'NULL',
            Ess_M2ePro_Model_Resource_Walmart_Dictionary_Category::COLUMN_TITLE
        );
        $tableModifier->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_Dictionary_Category::COLUMN_PRODUCT_TYPE_TITLE,
            'VARCHAR(255)',
            'NULL',
            Ess_M2ePro_Model_Resource_Walmart_Dictionary_Category::COLUMN_PRODUCT_TYPE_NICK
        );
    }

    private function createDictionaryProductType()
    {
        $walmartDictionaryProductTypeTableName = $this->_installer->getFullTableName('walmart_dictionary_product_type');
        if ($this->_installer->tableExists($walmartDictionaryProductTypeTableName)) {
            return;
        }

        $walmartDictionaryProductTypeTable = $this->_installer->getConnection()->newTable(
            $walmartDictionaryProductTypeTableName
        );
        $walmartDictionaryProductTypeTable->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType::COLUMN_ID,
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
                'auto_increment' => true,
            )
        );
        $walmartDictionaryProductTypeTable->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType::COLUMN_MARKETPLACE_ID,
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array('unsigned' => true, 'nullable' => false)
        );
        $walmartDictionaryProductTypeTable->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType::COLUMN_NICK,
            Varien_Db_Ddl_Table::TYPE_TEXT,
            255,
            array('nullable' => false)
        );
        $walmartDictionaryProductTypeTable->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType::COLUMN_TITLE,
            Varien_Db_Ddl_Table::TYPE_TEXT,
            255,
            array('nullable' => false)
        );
        $walmartDictionaryProductTypeTable->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType::COLUMN_ATTRIBUTES,
            Varien_Db_Ddl_Table::TYPE_TEXT,
            self::LONG_COLUMN_SIZE,
            array('nullable' => false)
        );
        $walmartDictionaryProductTypeTable->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType::COLUMN_VARIATION_ATTRIBUTES,
            Varien_Db_Ddl_Table::TYPE_TEXT,
            self::LONG_COLUMN_SIZE,
            array('nullable' => false)
        );
        $walmartDictionaryProductTypeTable->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType::COLUMN_INVALID,
            Varien_Db_Ddl_Table::TYPE_SMALLINT,
            null,
            array('unsigned' => true, 'nullable' => false, 'default' => 0)
        );
        $walmartDictionaryProductTypeTable->addIndex(
            Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType::COLUMN_MARKETPLACE_ID
            . '__'
            . Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType::COLUMN_NICK,
            array(
                Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType::COLUMN_MARKETPLACE_ID,
                Ess_M2ePro_Model_Resource_Walmart_Dictionary_ProductType::COLUMN_NICK,
            ),
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        );
        $walmartDictionaryProductTypeTable->setOption('type', 'INNODB');
        $walmartDictionaryProductTypeTable->setOption('charset', 'utf8');
        $walmartDictionaryProductTypeTable->setOption('collate', 'utf8_general_ci');
        $walmartDictionaryProductTypeTable->setOption('row_format', 'dynamic');

        $this->_installer->getConnection()->createTable($walmartDictionaryProductTypeTable);
    }

    private function createProductType()
    {
        $walmartProductTypeTableName = $this->_installer->getFullTableName('walmart_product_type');
        if ($this->_installer->tableExists($walmartProductTypeTableName)) {
            return;
        }

        $walmartProductTypeTable = $this->_installer->getConnection()->newTable(
            $walmartProductTypeTableName
        );
        $walmartProductTypeTable->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_ProductType::COLUMN_ID,
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
                'auto_increment' => true,
            )
        );
        $walmartProductTypeTable->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_ProductType::COLUMN_TITLE,
            Varien_Db_Ddl_Table::TYPE_TEXT,
            255,
            array('default' => null)
        );
        $walmartProductTypeTable->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_ProductType::COLUMN_DICTIONARY_PRODUCT_TYPE_ID,
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array('unsigned' => true, 'nullable' => false)
        );
        $walmartProductTypeTable->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_ProductType::COLUMN_ATTRIBUTES_SETTINGS,
            Varien_Db_Ddl_Table::TYPE_TEXT,
            self::LONG_COLUMN_SIZE,
            array('nullable' => false)
        );
        $walmartProductTypeTable->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_ProductType::COLUMN_UPDATE_DATE,
            Varien_Db_Ddl_Table::TYPE_DATETIME,
            null,
            array('default' => null)
        );
        $walmartProductTypeTable->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_ProductType::COLUMN_CREATE_DATE,
            Varien_Db_Ddl_Table::TYPE_DATETIME,
            null,
            array('default' => null)
        );
        $walmartProductTypeTable->addIndex(
            Ess_M2ePro_Model_Resource_Walmart_ProductType::COLUMN_DICTIONARY_PRODUCT_TYPE_ID,
            Ess_M2ePro_Model_Resource_Walmart_ProductType::COLUMN_DICTIONARY_PRODUCT_TYPE_ID,
            array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
        );
        $walmartProductTypeTable->addIndex(
            Ess_M2ePro_Model_Resource_Walmart_ProductType::COLUMN_TITLE,
            Ess_M2ePro_Model_Resource_Walmart_ProductType::COLUMN_TITLE
        );
        $walmartProductTypeTable->setOption('type', 'INNODB');
        $walmartProductTypeTable->setOption('charset', 'utf8');
        $walmartProductTypeTable->setOption('collate', 'utf8_general_ci');
        $walmartProductTypeTable->setOption('row_format', 'dynamic');

        $this->_installer->getConnection()->createTable($walmartProductTypeTable);
    }

    private function insertIntoWizard()
    {
        $wizardTableName = $this->_installer->getFullTableName('wizard');
        $nick = 'walmartMigrationToProductTypes';

        $query = $this->_installer->getConnection()
            ->select()
            ->from($wizardTableName)
            ->where('nick = ?', $nick)
            ->query();

        $row = $query->fetch();
        if ($row) {
            return;
        }

        $query = $this->_installer->getConnection()
                                  ->select()
                                  ->from(
                                      $this->_installer->getFullTableName('marketplace')
                                  )
                                  ->where('component_mode = "walmart" AND status = 1')
                                  ->query();

        $row = $query->fetch();
        $status = $row ? self::WIZARD_STATUS_ACTIVE : self::WIZARD_STATUS_SKIPPED;

        $this->_installer->getConnection()->insert(
            $wizardTableName,
            array(
                'nick' => $nick,
                'view' => 'walmart',
                'status' => $status,
                'step' => null,
                'type' => 1,
                'priority' => 12,
            )
        );
    }

    private function deleteAttributesFromDescriptionPolicy()
    {
        $tableModifier = $this->_installer->getTableModifier('walmart_template_description');
        $tableModifier->dropColumn('attributes_mode', false, false)
                      ->dropColumn('attributes', false, false)
                      ->commit();
    }

    private function deleteAttributesFromSellingPolicy()
    {
        $tableModifier = $this->_installer->getTableModifier('walmart_template_selling_format');
        $tableModifier->dropColumn('attributes_mode', false, false)
                      ->dropColumn('attributes', false, false)
                      ->commit();
    }
}
