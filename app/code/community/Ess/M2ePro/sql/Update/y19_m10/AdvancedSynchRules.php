<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m10_AdvancedSynchRules extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('ebay_template_synchronization')
            ->addColumn(
                'list_advanced_rules_mode',
                'TINYINT(2) UNSIGNED NOT NULL',
                null,
                'list_qty_calculated_value_max',
                false,
                false
            )
            ->addColumn(
                'relist_advanced_rules_mode',
                'TINYINT(2) UNSIGNED NOT NULL',
                null,
                'relist_qty_calculated_value_max',
                false,
                false
            )
            ->addColumn(
                'stop_advanced_rules_mode',
                'TINYINT(2) UNSIGNED NOT NULL',
                null,
                'stop_qty_calculated_value_max',
                false,
                false
            )
            ->addColumn(
                'list_advanced_rules_filters',
                'TEXT',
                null,
                'list_advanced_rules_mode',
                false,
                false
            )
            ->addColumn(
                'relist_advanced_rules_filters',
                'TEXT',
                null,
                'relist_advanced_rules_mode',
                false,
                false
            )
            ->addColumn(
                'stop_advanced_rules_filters',
                'TEXT',
                null,
                'stop_advanced_rules_mode',
                false,
                false
            )
            ->commit();

        $this->_installer->getTableModifier('amazon_template_synchronization')
            ->addColumn(
                'list_advanced_rules_mode',
                'TINYINT(2) UNSIGNED NOT NULL',
                null,
                'list_qty_calculated_value_max',
                false,
                false
            )
            ->addColumn(
                'relist_advanced_rules_mode',
                'TINYINT(2) UNSIGNED NOT NULL',
                null,
                'relist_qty_calculated_value_max',
                false,
                false
            )
            ->addColumn(
                'stop_advanced_rules_mode',
                'TINYINT(2) UNSIGNED NOT NULL',
                null,
                'stop_qty_calculated_value_max',
                false,
                false
            )
            ->addColumn(
                'list_advanced_rules_filters',
                'TEXT',
                null,
                'list_advanced_rules_mode',
                false,
                false
            )
            ->addColumn(
                'relist_advanced_rules_filters',
                'TEXT',
                null,
                'relist_advanced_rules_mode',
                false,
                false
            )
            ->addColumn(
                'stop_advanced_rules_filters',
                'TEXT',
                null,
                'stop_advanced_rules_mode',
                false,
                false
            )
            ->commit();

        $this->_installer->getTableModifier('walmart_template_synchronization')
            ->addColumn(
                'list_advanced_rules_mode',
                'TINYINT(2) UNSIGNED NOT NULL',
                null,
                'list_qty_calculated_value_max',
                false,
                false
            )
            ->addColumn(
                'relist_advanced_rules_mode',
                'TINYINT(2) UNSIGNED NOT NULL',
                null,
                'relist_qty_calculated_value_max',
                false,
                false
            )
            ->addColumn(
                'stop_advanced_rules_mode',
                'TINYINT(2) UNSIGNED NOT NULL',
                null,
                'stop_qty_calculated_value_max',
                false,
                false
            )
            ->addColumn(
                'list_advanced_rules_filters',
                'TEXT',
                null,
                'list_advanced_rules_mode',
                false,
                false
            )
            ->addColumn(
                'relist_advanced_rules_filters',
                'TEXT',
                null,
                'relist_advanced_rules_mode',
                false,
                false
            )
            ->addColumn(
                'stop_advanced_rules_filters',
                'TEXT',
                null,
                'stop_advanced_rules_mode',
                false,
                false
            )
            ->commit();
    }

    //########################################
}
