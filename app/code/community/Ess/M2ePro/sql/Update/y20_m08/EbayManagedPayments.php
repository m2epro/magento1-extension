<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m08_EbayManagedPayments extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('ebay_template_payment')->addColumn(
            'managed_payments_mode',
            'TINYINT(2) UNSIGNED NOT NULL',
            '0',
            'is_custom_template'
        );

        $tableModifier = $this->_installer->getTableModifier('ebay_marketplace');

        if ($tableModifier->isColumnExists('is_managed_payments')) {
            return;
        }

        $tableModifier->addColumn(
            'is_managed_payments',
            'TINYINT(2) UNSIGNED NOT NULL',
            '0',
            'is_metric_measurement_system',
            true
        );

        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName('ebay_marketplace'),
            array('is_managed_payments' => 1),
            array('marketplace_id IN (?)' => array(1, 2, 3, 4, 8))
        );
    }

    //########################################
}
