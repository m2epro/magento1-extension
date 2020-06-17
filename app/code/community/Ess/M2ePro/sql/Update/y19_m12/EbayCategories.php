<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m12_EbayCategories
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function getBackupTables()
    {
        return array(
            'ebay_template_category',
            'ebay_template_other_category',
        );
    }

    public function execute()
    {
        $this->_installer->getMainConfigModifier()
            ->delete('/view/ebay/template/category/', 'use_last_specifics');

        $stmt = $this->_installer->getConnection()
            ->select()
            ->from($this->_installer->getTable('m2epro_listing'))
            ->where('component_mode = ?', 'ebay')
            ->query();

        while ($row = $stmt->fetch()) {
            $additionalData = Mage::helper('M2ePro')->jsonDecode($row['additional_data']);
            unset($additionalData['mode_same_category_data']);
            unset($additionalData['ebay_primary_category']);
            unset($additionalData['ebay_store_primary_category']);

            $this->_installer->getConnection()
                ->update(
                    $this->_installer->getTable('m2epro_listing'),
                    array('additional_data' => Mage::helper('M2ePro')->jsonEncode($additionalData)),
                    array('id = ?' => $row['id'])
                );
        }
        //----------------------------------------

        $this->createColumns();

        $this->processCategoryTemplates();
        $this->processOtherCategoryTemplates();

        $this->removeColumns();
    }

    //########################################

    private function createColumns()
    {
        $this->_installer->getTableModifier('ebay_template_category')
            ->addColumn(
                'is_custom_template',
                'TINYINT(2) UNSIGNED NOT NULL',
                '0',
                'marketplace_id',
                true
            );

        //----------------------------------------

        $this->_installer->getTablesObject()->renameTable(
            'ebay_template_other_category',
            'ebay_template_store_category'
        );

        $this->_installer->getTableModifier('ebay_template_store_category')
            ->addColumn(
                'category_id',
                'INT(11) UNSIGNED NOT NULL',
                null,
                'account_id',
                false,
                false
            )
            ->addColumn(
                'category_path',
                'VARCHAR(255)',
                'NULL',
                'category_id',
                false,
                false
            )
            ->addColumn(
                'category_mode',
                'TINYINT(2) UNSIGNED NOT NULL',
                2,
                'category_path',
                false,
                false
            )
            ->addColumn(
                'category_attribute',
                'VARCHAR(255) NOT NULL',
                null,
                'category_mode',
                false,
                false
            )
            ->commit();

        //----------------------------------------

        $this->_installer->getTableModifier('ebay_listing_product')
            ->addColumn(
                'template_category_secondary_id',
                'INT(11) UNSIGNED',
                'NULL',
                'template_category_id',
                true,
                false
            )
            ->addColumn(
                'template_store_category_id',
                'INT(11) UNSIGNED',
                'NULL',
                'template_category_secondary_id',
                true,
                false
            )
            ->addColumn(
                'template_store_category_secondary_id',
                'INT(11) UNSIGNED',
                'NULL',
                'template_store_category_id',
                true,
                false
            )
            ->commit();

        $this->_installer->getTableModifier('ebay_listing')
            ->addColumn(
                'auto_global_adding_template_category_secondary_id',
                'INT(11) UNSIGNED',
                'NULL',
                'auto_global_adding_template_category_id',
                true,
                false
            )
            ->addColumn(
                'auto_global_adding_template_store_category_id',
                'INT(11) UNSIGNED',
                'NULL',
                'auto_global_adding_template_category_secondary_id',
                true,
                false
            )
            ->addColumn(
                'auto_global_adding_template_store_category_secondary_id',
                'INT(11) UNSIGNED',
                'NULL',
                'auto_global_adding_template_store_category_id',
                true,
                false
            )
            ->addColumn(
                'auto_website_adding_template_category_secondary_id',
                'INT(11) UNSIGNED',
                'NULL',
                'auto_website_adding_template_category_id',
                true,
                false
            )
            ->addColumn(
                'auto_website_adding_template_store_category_id',
                'INT(11) UNSIGNED',
                'NULL',
                'auto_website_adding_template_category_secondary_id',
                true,
                false
            )
            ->addColumn(
                'auto_website_adding_template_store_category_secondary_id',
                'INT(11) UNSIGNED',
                'NULL',
                'auto_website_adding_template_store_category_id',
                true,
                false
            )
            ->commit();

        $this->_installer->getTableModifier('ebay_listing_auto_category_group')
            ->addColumn(
                'adding_template_category_secondary_id',
                'INT(11) UNSIGNED',
                'NULL',
                'adding_template_category_id',
                true,
                false
            )
            ->addColumn(
                'adding_template_store_category_id',
                'INT(11) UNSIGNED',
                'NULL',
                'adding_template_category_secondary_id',
                true,
                false
            )
            ->addColumn(
                'adding_template_store_category_secondary_id',
                'INT(11) UNSIGNED',
                'NULL',
                'adding_template_store_category_id',
                true,
                false
            )
            ->commit();
    }

    private function removeColumns()
    {
        $this->_installer->getTableModifier('ebay_listing_product')
            ->dropColumn('template_other_category_id');

        $this->_installer->getTableModifier('ebay_listing')
            ->dropColumn('auto_global_adding_template_other_category_id', true, false)
            ->dropColumn('auto_website_adding_template_other_category_id', true, false)
            ->commit();

        $this->_installer->getTableModifier('ebay_listing_auto_category_group')
            ->dropColumn('adding_template_other_category_id');
    }

    //########################################

    private function processCategoryTemplates()
    {
        $modifier = $this->_installer->getTableModifier('ebay_template_category');
        if (!$modifier->isColumnExists('category_main_attribute')) {
            return;
        }

        $this->_installer->getConnection()->update(
            $this->_installer->getTable('m2epro_ebay_template_category'),
            array('is_custom_template' => 1)
        );

        $stmt = $this->_installer->getConnection()
            ->select()
            ->from(
                array('metc' => $this->_installer->getTable('m2epro_ebay_template_category'))
            )
            ->joinInner(
                array('melp' => $this->_installer->getTable('m2epro_ebay_listing_product')),
                'melp.template_category_id=metc.id'
            )
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(
                array(
                    'template_id'    => 'metc.id',
                    'template_value' => new Zend_Db_Expr(
                        'IF(metc.category_main_mode = 1, metc.category_main_id, metc.category_main_attribute)'
                    ),
                    'usages' => new Zend_Db_Expr('COUNT(melp.listing_product_id)')
                )
            )
            ->group(array('metc.id'))
            ->query();

        $mostUsed = array();
        while ($row = $stmt->fetch()) {
            if (!isset($mostUsed[$row['template_value']]) ||
                $row['usages'] > $mostUsed[$row['template_value']]['usages']
            ) {
                $mostUsed[$row['template_value']] = $row;
            }
        }

        foreach ($mostUsed as $categoryValue => $data) {
            $this->_installer->getConnection()->update(
                $this->_installer->getTable('m2epro_ebay_template_category'),
                array('is_custom_template' => 0),
                array('id = ?' => $data['template_id'])
            );
        }

        $this->_installer->getTableModifier('ebay_template_category')
            ->renameColumn('category_main_id', 'category_id', true, false)
            ->renameColumn('category_main_path', 'category_path', true, false)
            ->renameColumn('category_main_mode', 'category_mode', true, false)
            ->renameColumn('category_main_attribute', 'category_attribute', true, false)
            ->commit();
    }

    private function processOtherCategoryTemplates()
    {
        $this->processCategorySecondaryTemplates();
        $this->processStoreCategoryTemplates();
        $this->processStoreCategorySecondaryTemplates();

        $this->_installer->getConnection()->delete(
            $this->_installer->getTable('m2epro_ebay_template_store_category'),
            "category_id = 0 AND category_attribute = ''"
        );
    }

    private function processCategorySecondaryTemplates()
    {
        $modifier = $this->_installer->getTableModifier('ebay_template_store_category');
        if (!$modifier->isColumnExists('category_secondary_mode')) {
            return;
        }

        $stmt = $this->_installer->getConnection()
            ->select()
            ->from(
                array('metsc' => $this->_installer->getTable('m2epro_ebay_template_store_category'))
            )
            ->where('category_secondary_mode != 0')
            ->query();

        while ($row = $stmt->fetch()) {

            $this->_installer->getConnection()->insert(
                $this->_installer->getTable('m2epro_ebay_template_category'),
                array(
                    'is_custom_template' => '1',
                    'marketplace_id'     => $row['marketplace_id'],
                    'category_id'        => $row['category_secondary_id'],
                    'category_path'      => $row['category_secondary_path'],
                    'category_mode'      => $row['category_secondary_mode'],
                    'category_attribute' => $row['category_secondary_attribute'],
                    'create_date'        => $row['create_date'],
                    'update_date'        => $row['update_date']
                )
            );
            $newId = $this->_installer->getConnection()->lastInsertId();

            $this->_installer->getConnection()->update(
                $this->_installer->getTable('m2epro_ebay_listing_product'),
                array('template_category_secondary_id' => $newId),
                array('template_other_category_id = ?' => $row['id'])
            );

            $this->_installer->getConnection()->update(
                $this->_installer->getTable('m2epro_ebay_listing'),
                array('auto_global_adding_template_category_secondary_id' => $newId),
                array('auto_global_adding_template_other_category_id = ?' => $row['id'])
            );

            $this->_installer->getConnection()->update(
                $this->_installer->getTable('m2epro_ebay_listing'),
                array('auto_website_adding_template_category_secondary_id' => $newId),
                array('auto_website_adding_template_other_category_id = ?' => $row['id'])
            );

            $this->_installer->getConnection()->update(
                $this->_installer->getTable('m2epro_ebay_listing_auto_category_group'),
                array('adding_template_category_secondary_id' => $newId),
                array('adding_template_other_category_id = ?' => $row['id'])
            );
        }

        $this->_installer->getTableModifier('ebay_template_store_category')
            ->dropColumn('marketplace_id', true, false)
            ->dropColumn('category_secondary_id', true, false)
            ->dropColumn('category_secondary_path', true, false)
            ->dropColumn('category_secondary_mode', true, false)
            ->dropColumn('category_secondary_attribute', true, false)
            ->commit();
    }

    private function processStoreCategoryTemplates()
    {
        $modifier = $this->_installer->getTableModifier('ebay_template_store_category');
        if (!$modifier->isColumnExists('store_category_main_mode')) {
            return;
        }

        $stmt = $this->_installer->getConnection()
            ->select()
            ->from($this->_installer->getTable('m2epro_ebay_template_store_category'))
            ->where('store_category_main_mode != 0')
            ->query();

        while ($row = $stmt->fetch()) {

            $this->_installer->getConnection()->insert(
                $this->_installer->getTable('m2epro_ebay_template_store_category'),
                array(
                    'account_id'         => $row['account_id'],
                    'category_id'        => $row['store_category_main_id'],
                    'category_path'      => $row['store_category_main_path'],
                    'category_mode'      => $row['store_category_main_mode'],
                    'category_attribute' => $row['store_category_main_attribute'],
                    'create_date'        => $row['create_date'],
                    'update_date'        => $row['update_date']
                )
            );
            $newId = $this->_installer->getConnection()->lastInsertId();

            $this->_installer->getConnection()->update(
                $this->_installer->getTable('m2epro_ebay_listing_product'),
                array('template_store_category_id' => $newId),
                array('template_other_category_id = ?' => $row['id'])
            );

            $this->_installer->getConnection()->update(
                $this->_installer->getTable('m2epro_ebay_listing'),
                array('auto_global_adding_template_store_category_id' => $newId),
                array('auto_global_adding_template_other_category_id = ?' => $row['id'])
            );

            $this->_installer->getConnection()->update(
                $this->_installer->getTable('m2epro_ebay_listing'),
                array('auto_website_adding_template_store_category_id' => $newId),
                array('auto_website_adding_template_other_category_id = ?' => $row['id'])
            );

            $this->_installer->getConnection()->update(
                $this->_installer->getTable('m2epro_ebay_listing_auto_category_group'),
                array('adding_template_store_category_id' => $newId),
                array('adding_template_other_category_id = ?' => $row['id'])
            );
        }

        $this->_installer->getTableModifier('ebay_template_store_category')
            ->dropColumn('store_category_main_id', true, false)
            ->dropColumn('store_category_main_path', true, false)
            ->dropColumn('store_category_main_mode', true, false)
            ->dropColumn('store_category_main_attribute', true, false)
            ->commit();
    }

    private function processStoreCategorySecondaryTemplates()
    {
        $modifier = $this->_installer->getTableModifier('ebay_template_store_category');
        if (!$modifier->isColumnExists('store_category_secondary_mode')) {
            return;
        }

        $stmt = $this->_installer->getConnection()
            ->select()
            ->from($this->_installer->getTable('m2epro_ebay_template_store_category'))
            ->where('store_category_secondary_mode != 0')
            ->query();

        while ($row = $stmt->fetch()) {

            $this->_installer->getConnection()->insert(
                $this->_installer->getTable('m2epro_ebay_template_store_category'),
                array(
                    'account_id'         => $row['account_id'],
                    'category_id'        => $row['store_category_secondary_id'],
                    'category_path'      => $row['store_category_secondary_path'],
                    'category_mode'      => $row['store_category_secondary_mode'],
                    'category_attribute' => $row['store_category_secondary_attribute'],
                    'create_date'        => $row['create_date'],
                    'update_date'        => $row['update_date']
                )
            );
            $newId = $this->_installer->getConnection()->lastInsertId();

            $this->_installer->getConnection()->update(
                $this->_installer->getTable('m2epro_ebay_listing_product'),
                array('template_store_category_secondary_id' => $newId),
                array('template_other_category_id = ?' => $row['id'])
            );

            $this->_installer->getConnection()->update(
                $this->_installer->getTable('m2epro_ebay_listing'),
                array('auto_global_adding_template_store_category_secondary_id' => $newId),
                array('auto_global_adding_template_other_category_id = ?' => $row['id'])
            );

            $this->_installer->getConnection()->update(
                $this->_installer->getTable('m2epro_ebay_listing'),
                array('auto_website_adding_template_store_category_secondary_id' => $newId),
                array('auto_website_adding_template_other_category_id = ?' => $row['id'])
            );

            $this->_installer->getConnection()->update(
                $this->_installer->getTable('m2epro_ebay_listing_auto_category_group'),
                array('adding_template_store_category_secondary_id' => $newId),
                array('adding_template_other_category_id = ?' => $row['id'])
            );
        }

        $this->_installer->getTableModifier('ebay_template_store_category')
            ->dropColumn('store_category_secondary_id', true, false)
            ->dropColumn('store_category_secondary_path', true, false)
            ->dropColumn('store_category_secondary_mode', true, false)
            ->dropColumn('store_category_secondary_attribute', true, false)
            ->commit();
    }

    //########################################
}
