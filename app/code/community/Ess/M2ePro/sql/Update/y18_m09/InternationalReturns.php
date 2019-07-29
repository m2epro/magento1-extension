<?php

class Ess_M2ePro_Sql_Update_y18_m09_InternationalReturns extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->installer->getTableModifier('ebay_template_return')
            ->addColumn('international_accepted', 'VARCHAR(255) NOT NULL', NULL, 'shipping_cost', false, false)
            ->addColumn('international_option', 'VARCHAR(255) NOT NULL', NULL, 'international_accepted', false, false)
            ->addColumn('international_within', 'VARCHAR(255) NOT NULL', NULL, 'international_option', false, false)
            ->addColumn(
                'international_shipping_cost', 'VARCHAR(255) NOT NULL', NULL, 'international_within', false, false
            )
            ->dropColumn('holiday_mode', false, false)
            ->dropColumn('restocking_fee', false, false)
            ->commit();

        $this->installer->getTableModifier('ebay_marketplace')
            ->dropColumn('is_holiday_return', true, false)
            ->addColumn('is_return_description', 'TINYINT(2) UNSIGNED NOT NULL', '0', 'is_in_store_pickup', true, false)
            ->commit();

        $this->installer->run(<<<SQL
UPDATE `m2epro_ebay_template_return`
SET `international_accepted` = 'ReturnsNotAccepted';
  
UPDATE `m2epro_ebay_marketplace`
SET `is_return_description` = 1
WHERE `marketplace_id` IN (8, 13, 7, 10, 5);
SQL
        );
    }

    //########################################
}