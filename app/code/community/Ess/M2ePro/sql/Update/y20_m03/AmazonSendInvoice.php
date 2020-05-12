<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m03_AmazonSendInvoice extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('amazon_account')
            ->renameColumn(
                'is_vat_calculation_service_enabled',
                'auto_invoicing',
                true,
                false
            )
            ->commit();

        $this->_installer->getTableModifier('amazon_order')
            ->addColumn('is_invoice_sent', 'TINYINT(2) UNSIGNED NOT NULL', '0', 'status', true, false)
            ->addColumn('is_credit_memo_sent', 'TINYINT(2) UNSIGNED NOT NULL', '0', 'is_invoice_sent', true, false)
            ->commit();

        $this->_installer->getTableModifier('amazon_marketplace')
            ->addColumn(
                'is_upload_invoices_available',
                'TINYINT(2) UNSIGNED NOT NULL',
                '0',
                'is_automatic_token_retrieving_available',
                true,
                false
            )
            ->commit();

        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName('amazon_marketplace'),
            array('is_upload_invoices_available' => 1),
            array('marketplace_id IN (?)' => array(25, 26, 28, 30, 31))
        );
    }

    //########################################
}