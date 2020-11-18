<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Sql_Update_y20_m08_InventorySynchronization extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Exception
     * @throws Zend_Db_Statement_Exception
     */
    public function execute()
    {
        $this->processAmazon();
        $this->processWalmart();
        $this->processEbay();
    }

    /**
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Exception
     * @throws Zend_Db_Statement_Exception
     */
    protected function processAmazon()
    {
        $this->_installer->run(
            <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->_installer->getTable('m2epro_amazon_inventory_sku')}` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `account_id` INT(11) UNSIGNED NOT NULL,
    `sku` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `account_id__sku` (`account_id`, `sku`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
        );

        $this->_installer->getTableModifier('amazon_account')
            ->addColumn('inventory_last_synchronization', 'DATETIME', 'NULL', 'other_listings_mapping_settings', true);

        $accountTable = $this->_installer->getTablesObject()->getFullName('account');

        $accountStmt = $this->_installer->getConnection()->select()
            ->from(
                $accountTable,
                array('id', 'additional_data')
            )
            ->where('component_mode = ?', 'amazon')
            ->query();

        while ($row = $accountStmt->fetch()) {
            $additionalData = (array)json_decode($row['additional_data'], true);
            unset(
                $additionalData['last_other_listing_products_synchronization'],
                $additionalData['last_listing_products_synchronization']
            );

            $this->_installer->getConnection()->update(
                $accountTable,
                array('additional_data' => json_encode($additionalData)),
                array('id = ?' => (int)$row['id'])
            );
        }

        $this->_installer->getTableModifier('amazon_listing_product')
            ->addColumn('list_date', 'DATETIME', 'NULL', 'defected_messages', true);

        $productsStmt = $this->_installer->getConnection()->select()
            ->from(
                $this->_installer->getTablesObject()->getFullName('listing_product'),
                array('id', 'additional_data')
            )
            ->where('component_mode = ?', 'amazon')
            ->where('additional_data LIKE ?', '%"list_date":%')
            ->query();

        $now = new DateTime('now', new DateTimeZone('UTC'));

        $this->_installer->getConnection()->update(
            $this->_installer->getTablesObject()->getFullName('amazon_listing_product'),
            array('list_date' => $now->format('Y-m-d H:i:s'))
        );

        while ($row = $productsStmt->fetch()) {
            $additionalData = (array)json_decode($row['additional_data'], true);
            if (empty($additionalData['list_date'])) {
                continue;
            }

            unset($additionalData['list_date']);
            $additionalData = json_encode($additionalData);

            $this->_installer->getConnection()->update(
                $this->_installer->getTablesObject()->getFullName('listing_product'),
                array('additional_data' => $additionalData),
                array('id = ?' => (int)$row['id'])
            );
        }

        $tableModifier = $this->_installer->getTableModifier('m2epro_amazon_listing_other');
        $tableModifier->dropIndex('title');
        $tableModifier->changeColumn('title', 'TEXT', 'NULL', null);

        $this->_installer->run(<<<SQL
ALTER TABLE `{$this->_installer->getTable('m2epro_amazon_listing_other')}` ADD INDEX `title` (`title`(255))
SQL
        );
    }

    /**
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Exception
     */
    protected function processWalmart()
    {
        $this->_installer->run(
            <<<SQL
CREATE TABLE IF NOT EXISTS `{$this->_installer->getTable('m2epro_walmart_inventory_wpid')}` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `account_id` INT(11) UNSIGNED NOT NULL,
    `wpid` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `account_id__wpid` (`account_id`, `wpid`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
        );

        $this->_installer->getTableModifier('walmart_account')
            ->addColumn('inventory_last_synchronization', 'DATETIME', 'NULL', 'orders_last_synchronization', true);
    }

    /**
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Exception
     */
    protected function processEbay()
    {
        $this->_installer->getTableModifier('ebay_account')
            ->renameColumn(
                'defaults_last_synchronization',
                'inventory_last_synchronization',
                true,
                false
            )
            ->commit();
    }

    //########################################
}
