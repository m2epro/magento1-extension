<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m10_AddInvoiceAndShipment extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->addColumnToEbayAccount();
        $this->addColumnToWalmartAccount();
    }

    //########################################

    private function addColumnToEbayAccount()
    {
        if (!$this->_installer->getTableModifier('ebay_account')->isColumnExists('create_magento_invoice') &&
            !$this->_installer->getTableModifier('ebay_account')->isColumnExists('create_magento_shipment')) {

            $this->_installer->getTableModifier('ebay_account')
                ->addColumn(
                    'create_magento_invoice',
                    'TINYINT(2) UNSIGNED NOT NULL',
                    1,
                    'magento_orders_settings',
                    false,
                    false
                )
                ->addColumn(
                    'create_magento_shipment',
                    'TINYINT(2) UNSIGNED NOT NULL',
                    1,
                    'create_magento_invoice',
                    false,
                    false
                )
                ->commit();

            $ebayAccountTable = $this->_installer->getFullTableName('ebay_account');

            $query = $this->_installer->getConnection()
                ->select()
                ->from($ebayAccountTable)
                ->query();

            while ($row = $query->fetch()) {
                $magentoOrdersSettings = Mage::helper('M2ePro/Data')->jsonDecode($row['magento_orders_settings']);

                $data = array(
                    'create_magento_invoice' => empty($magentoOrdersSettings['invoice_mode']) ?
                        0 : $magentoOrdersSettings['invoice_mode'],
                    'create_magento_shipment' => empty($magentoOrdersSettings['shipment_mode']) ?
                        0 : $magentoOrdersSettings['shipment_mode']
                );

                // clearing old data
                unset($magentoOrdersSettings['invoice_mode']);
                unset($magentoOrdersSettings['shipment_mode']);
                $data['magento_orders_settings'] = Mage::helper('M2ePro/Data')->jsonEncode($magentoOrdersSettings);

                $this->_installer->getConnection()->update(
                    $ebayAccountTable,
                    $data,
                    array('account_id = ?' => (int)$row['account_id'])
                );
            }
        }
    }

    //########################################

    private function addColumnToWalmartAccount()
    {
        if (!$this->_installer->getTableModifier('walmart_account')->isColumnExists('create_magento_invoice') &&
            !$this->_installer->getTableModifier('walmart_account')->isColumnExists('create_magento_shipment')) {

            $this->_installer->getTableModifier('walmart_account')
                ->addColumn(
                    'create_magento_invoice',
                    'TINYINT(2) UNSIGNED NOT NULL',
                    1,
                    'magento_orders_settings',
                    false,
                    false
                )
                ->addColumn(
                    'create_magento_shipment',
                    'TINYINT(2) UNSIGNED NOT NULL',
                    1,
                    'create_magento_invoice',
                    false,
                    false
                )
                ->commit();

            $ebayAccountTable = $this->_installer->getFullTableName('walmart_account');

            $query = $this->_installer->getConnection()
                ->select()
                ->from($ebayAccountTable)
                ->query();

            while ($row = $query->fetch()) {
                $magentoOrdersSettings = Mage::helper('M2ePro/Data')->jsonDecode($row['magento_orders_settings']);

                $data = array(
                    'create_magento_invoice' => empty($magentoOrdersSettings['invoice_mode']) ?
                        0 : $magentoOrdersSettings['invoice_mode'],
                    'create_magento_shipment' => empty($magentoOrdersSettings['shipment_mode']) ?
                        0 : $magentoOrdersSettings['shipment_mode']
                );

                // clearing old data
                unset($magentoOrdersSettings['invoice_mode']);
                unset($magentoOrdersSettings['shipment_mode']);
                $data['magento_orders_settings'] = Mage::helper('M2ePro/Data')->jsonEncode($magentoOrdersSettings);

                $this->_installer->getConnection()->update(
                    $ebayAccountTable,
                    $data,
                    array('account_id = ?' => (int)$row['account_id'])
                );
            }
        }
    }

    //########################################
}
