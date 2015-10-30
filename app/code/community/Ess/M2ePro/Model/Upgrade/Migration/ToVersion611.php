<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_Migration_ToVersion611
{
    const BACKUP_TABLE_PREFIX = '__backup_v611';

    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    private $installer = NULL;

    //########################################

    /**
     * @return Ess_M2ePro_Model_Upgrade_MySqlSetup
     */
    public function getInstaller()
    {
        return $this->installer;
    }

    /**
     * @param Ess_M2ePro_Model_Upgrade_MySqlSetup $installer
     */
    public function setInstaller(Ess_M2ePro_Model_Upgrade_MySqlSetup $installer)
    {
        $this->installer = $installer;
    }

    //########################################

    /*
        DELETE FROM `m2epro_wizard` WHERE (`nick` = 'amazonNewAsin' OR `nick` = 'buyNewSku');
        UPDATE `m2epro_wizard` SET `priority` = 5 WHERE (`priority` = 7);
    */

    //########################################

    public function migrate()
    {
        try {

            $this->prepareWizardsTable();

            $this->processProcessing();
            $this->processLogs();
            $this->processConfigData();

            $this->prepareOrdersTables();
            $this->prepareOrdersConfigTable();
            $this->processOrdersData();

        } catch (Exception $e) {
            echo $e->getMessage();
            die;
        }
    }

    //########################################

    private function prepareWizardsTable()
    {
        $connection = $this->installer->getConnection();
        $tempTable = $this->installer->getTable('m2epro_wizard');

        $connection->delete($tempTable, "`nick` = 'amazonNewAsin' OR `nick` = 'buyNewSku'");
        $connection->update($tempTable, array('priority' => 5), '`priority` = 7');
    }

    // ---------------------------------------

    private function processProcessing()
    {
        /** @var Ess_M2ePro_Model_Upgrade_Migration_ToVersion611_Processing $model */
        $model = Mage::getModel('M2ePro/Upgrade_Migration_ToVersion611_Processing');
        $model->setInstaller($this->installer);
        $model->process();
    }

    private function processLogs()
    {
        /** @var Ess_M2ePro_Model_Upgrade_Migration_ToVersion611_Logs $model */
        $model = Mage::getModel('M2ePro/Upgrade_Migration_ToVersion611_Logs');
        $model->setInstaller($this->installer);
        $model->process();
    }

    private function processConfigData()
    {
        /** @var Ess_M2ePro_Model_Upgrade_Migration_ToVersion611_ConfigData $model */
        $model = Mage::getModel('M2ePro/Upgrade_Migration_ToVersion611_ConfigData');
        $model->setInstaller($this->installer);
        $model->process();
    }

    // ---------------------------------------

    private function prepareOrdersTables()
    {
        $connection = $this->installer->getConnection();

        $orderTable = $this->installer->getTable('m2epro_ebay_order');
        $orderBackupOTable = $this->installer->getTable('m2epro'.self::BACKUP_TABLE_PREFIX.'_ebay_order');

        if ($this->installer->tableExists($orderTable) && !$this->installer->tableExists($orderBackupOTable)) {
            $connection->query("RENAME TABLE `{$orderTable}` TO `{$orderBackupOTable}`");
        }

        $orderItemTable = $this->installer->getTable('m2epro_ebay_order_item');
        $orderItemBackupTable = $this->installer->getTable('m2epro'.self::BACKUP_TABLE_PREFIX.'_ebay_order_item');

        if ($this->installer->tableExists($orderItemTable) && !$this->installer->tableExists($orderItemBackupTable)) {
            $connection->query("RENAME TABLE `{$orderItemTable}` TO `{$orderItemBackupTable}`");
        }

        $this->installer->run(<<<SQL
CREATE TABLE IF NOT EXISTS m2epro_ebay_order (
  order_id INT(11) UNSIGNED NOT NULL,
  ebay_order_id VARCHAR(255) NOT NULL,
  selling_manager_id INT(11) UNSIGNED DEFAULT NULL,
  buyer_name VARCHAR(255) NOT NULL,
  buyer_email VARCHAR(255) NOT NULL,
  buyer_user_id VARCHAR(255) NOT NULL,
  buyer_message VARCHAR(500) DEFAULT NULL,
  paid_amount DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  saved_amount DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  currency VARCHAR(10) NOT NULL,
  checkout_status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  shipping_status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  payment_status TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  shipping_details TEXT DEFAULT NULL,
  payment_details TEXT DEFAULT NULL,
  tax_details TEXT DEFAULT NULL,
  purchase_update_date DATETIME DEFAULT NULL,
  purchase_create_date DATETIME DEFAULT NULL,
  PRIMARY KEY (order_id),
  INDEX ebay_order_id (ebay_order_id),
  INDEX selling_manager_id (selling_manager_id),
  INDEX buyer_email (buyer_email),
  INDEX buyer_name (buyer_name),
  INDEX buyer_user_id (buyer_user_id),
  INDEX paid_amount (paid_amount),
  INDEX checkout_status (checkout_status),
  INDEX payment_status (payment_status),
  INDEX shipping_status (shipping_status)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

CREATE TABLE IF NOT EXISTS m2epro_ebay_order_item (
  order_item_id INT(11) UNSIGNED NOT NULL,
  transaction_id VARCHAR(20) NOT NULL,
  selling_manager_id INT(11) UNSIGNED DEFAULT NULL,
  item_id DECIMAL(20, 0) UNSIGNED NOT NULL,
  title VARCHAR(255) NOT NULL,
  sku VARCHAR(64) DEFAULT NULL,
  price DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  qty_purchased INT(11) UNSIGNED NOT NULL,
  tax_details TEXT DEFAULT NULL,
  final_fee DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  variation_details TEXT DEFAULT NULL,
  tracking_details TEXT DEFAULT NULL,
  unpaid_item_process_state TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (order_item_id),
  INDEX transaction_id (transaction_id),
  INDEX selling_manager_id (selling_manager_id),
  INDEX item_id (item_id),
  INDEX sku (sku),
  INDEX title (title),
  INDEX unpaid_item_process_state (unpaid_item_process_state)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;
SQL
);
    }

    private function prepareOrdersConfigTable()
    {
        $connection = $this->installer->getConnection();
        $tempTable = $this->installer->getTable('m2epro_config');

        $tempQuery = <<<SQL
    SELECT * FROM `{$tempTable}`
    WHERE `group` = '/ebay/order/migration_to_v611/'
    AND   `key` = 'is_need_migrate'
SQL;
        $tempRow = $connection->query($tempQuery)->fetch();

        if ($tempRow === false) {
            $this->installer->run(<<<SQL

INSERT INTO `m2epro_config` (`group`,`key`,`value`,`notice`,`update_date`,`create_date`) VALUES
('/ebay/order/migration_to_v611/', 'is_need_migrate', '1', null, '2013-09-18 00:00:00', '2013-09-18 00:00:00');

SQL
);
        }
    }

    private function processOrdersData()
    {
        /** @var Ess_M2ePro_Model_Upgrade_Migration_ToVersion611_OrdersData $migrationInstance */
        $migrationInstance = Mage::getModel('M2ePro/Upgrade_Migration_ToVersion611_OrdersData');
        $migrationInstance->setMaxBackDaysInterval(90);
        $migrationInstance->setMaxOrdersCount(10000);
        $migrationInstance->migrate();
    }

    //########################################
}