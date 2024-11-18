<?php

class Ess_M2ePro_Sql_Update_y24_m10_AddAmazonShippingTemplates
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        if ($this->isNeedSkipExecute()) {
            return;
        }

        $this->truncateAmazonTemplateShipping();
        $this->unsetTemplateShippingId();
        $this->changeSchemeAmazonTemplateShipping();
        $this->createTableAmazonDictionaryTemplateShipping();
    }

    /**
     * @return bool
     */
    private function isNeedSkipExecute()
    {
        $tableName = $this->_installer->getFullTableName(
            'amazon_dictionary_template_shipping'
        );

        return $this->_installer->tableExists($tableName);
    }

    /**
     * @return void
     */
    private function truncateAmazonTemplateShipping()
    {
        $this->_installer->getConnection()->truncateTable(
            $this->_installer->getFullTableName('amazon_template_shipping')
        );
    }

    /**
     * @return void
     */
    private function unsetTemplateShippingId()
    {
        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName('amazon_listing'),
            array('template_shipping_id' => null)
        );

        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName('amazon_listing_product'),
            array('template_shipping_id' => null)
        );
    }

    /**
     * @return void
     */
    private function changeSchemeAmazonTemplateShipping()
    {
        $tableModifier = $this->_installer->getTableModifier(
            'amazon_template_shipping'
        );
        $tableModifier->addColumn(
            'account_id',
            'INT(11) UNSIGNED NOT NULL',
            null,
            'title',
            false,
            false
        );
        $tableModifier->addColumn(
            'marketplace_id',
            'INT(11) UNSIGNED NOT NULL',
            null,
            'account_id',
            false,
            false
        );
        $tableModifier->addColumn(
            'template_id',
            'VARCHAR(255) NOT NULL',
            null,
            'marketplace_id',
            false,
            false
        );
        $tableModifier->dropColumn('template_name_mode', true, false);
        $tableModifier->dropColumn('template_name_value', true, false);
        $tableModifier->dropColumn('template_name_attribute', true, false);

        $tableModifier->commit();
    }

    /**
     * @return void
     */
    private function createTableAmazonDictionaryTemplateShipping()
    {
        $tableName = $this->_installer->getFullTableName(
            'amazon_dictionary_template_shipping'
        );

        $table = $this->_installer->getConnection()
                                  ->newTable($tableName);
        $table->addColumn(
            'id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array(
                'unsigned' => true,
                'primary' => true,
                'nullable' => false,
                'auto_increment' => true,
            )
        );
        $table->addColumn(
            'account_id',
            Varien_Db_Ddl_Table::TYPE_INTEGER,
            null,
            array('unsigned' => true, 'nullable' => false)
        );
        $table->addColumn(
            'template_id',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            255,
            array('nullable' => false)
        );
        $table->addColumn(
            'title',
            Varien_Db_Ddl_Table::TYPE_TEXT,
            255,
            array('nullable' => false)
        );
        $table->addIndex('account_id', 'account_id');
        $table->setOption('type', 'INNODB');
        $table->setOption('charset', 'utf8');
        $table->setOption('collate', 'utf8_general_ci');
        $table->setOption('row_format', 'dynamic');

        $this->_installer->getConnection()
                         ->createTable($table);
    }
}