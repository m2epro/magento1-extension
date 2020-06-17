<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Upgrade_v6_5_0_16__v6_5_6_InternationalReturns
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('ebay_template_return')
            ->addColumn('international_accepted', 'VARCHAR(255) NOT NULL', NULL, 'shipping_cost', false, false)
            ->addColumn('international_option', 'VARCHAR(255) NOT NULL', NULL,
                        'international_accepted', false, false)
            ->addColumn('international_within', 'VARCHAR(255) NOT NULL', NULL,
                        'international_option', false, false)
            ->addColumn('international_shipping_cost', 'VARCHAR(255) NOT NULL', NULL,
                        'international_within', false, false)
            ->dropColumn('holiday_mode', false, false)
            ->dropColumn('restocking_fee', false, false)
            ->commit();

        $this->_installer->getTableModifier('ebay_marketplace')
            ->dropColumn('is_holiday_return', true, false)
            ->addColumn('is_return_description', 'TINYINT(2) UNSIGNED NOT NULL', '0',
                        'is_in_store_pickup', true, false)
            ->commit();

        $this->_installer->getConnection()
            ->update(
                $this->_installer->getTablesObject()->getFullName('ebay_template_return'),
                array('international_accepted' => 'ReturnsNotAccepted')
            );

        $this->_installer->getConnection()
            ->update(
                $this->_installer->getTablesObject()->getFullName('ebay_marketplace'),
                array('is_return_description' => 1),
                array('marketplace_id IN (?)' => array(8, 13, 7, 10, 5))
            );
    }

    //########################################
}