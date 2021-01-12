<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m08_VCSLiteInvoices extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        if (!$this->_installer->tableExists($this->_installer->getTablesObject()->getFullName('amazon_order_invoice'))
        ) {
            $this->_installer->run(
                <<<SQL
CREATE TABLE `{$this->_installer->getTable('m2epro_amazon_order_invoice')}` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) UNSIGNED NOT NULL,
  `document_type` VARCHAR(64) DEFAULT NULL,
  `document_number` VARCHAR(64) DEFAULT NULL,
  `document_data` LONGTEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `order_id` (`order_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;
SQL
            );
        }

        $this->_installer->getTableModifier('amazon_account')
            ->addColumn(
                'invoice_generation', 'TINYINT(2) UNSIGNED NOT NULL', 0, 'auto_invoicing', false, false
            )
            ->addColumn(
                'create_magento_shipment',
                'TINYINT(2) UNSIGNED NOT NULL',
                1,
                'is_magento_invoice_creation_disabled',
                false,
                false
            )
            ->commit();

        $this->_installer->getTableModifier('amazon_order')
            ->addColumn(
                'invoice_data_report', 'LONGTEXT', 'NULL', 'is_credit_memo_sent', false, false
            )
            ->commit();

        if ($this->_installer->getTableModifier('amazon_account')
            ->isColumnExists('is_magento_invoice_creation_disabled')) {

            $amazonAccountTable = $this->_installer->getFullTableName('amazon_account');

            $query = $this->_installer->getConnection()
                ->select()
                ->from($amazonAccountTable)
                ->query();

            while ($row = $query->fetch()) {
                $magentoOrdersSettings = Mage::helper('M2ePro/Data')->jsonDecode($row['magento_orders_settings']);

                $data = array(
                    'is_magento_invoice_creation_disabled' => empty($magentoOrdersSettings['invoice_mode']) ?
                        0 : $magentoOrdersSettings['invoice_mode'],
                    'create_magento_shipment'              => empty($magentoOrdersSettings['shipment_mode']) ?
                        0 : $magentoOrdersSettings['shipment_mode']
                );

                // if VCS was enabled
                if ($row['auto_invoicing'] == 1) {
                    // revert old "is disabled" value
                    $data['is_magento_invoice_creation_disabled'] = !$row['is_magento_invoice_creation_disabled'];
                }

                // clearing old data
                unset($magentoOrdersSettings['invoice_mode']);
                unset($magentoOrdersSettings['shipment_mode']);
                $data['magento_orders_settings'] = Mage::helper('M2ePro/Data')->jsonEncode($magentoOrdersSettings);

                $this->_installer->getConnection()->update(
                    $amazonAccountTable,
                    $data,
                    array('account_id = ?' => (int)$row['account_id'])
                );
            }

            $this->_installer->getTableModifier('amazon_account')
                ->changeColumn('is_magento_invoice_creation_disabled', 'TINYINT(2) UNSIGNED NOT NULL', 1, null, false)
                ->commit();
            $this->_installer->getTableModifier('amazon_account')
                ->renameColumn('is_magento_invoice_creation_disabled', 'create_magento_invoice', false, false)
                ->commit();
        }
    }

    //########################################
}
