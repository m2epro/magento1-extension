<?php

class Ess_M2ePro_Sql_Upgrade_v6_5_1_0__v6_5_2_0_AllFeatures extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $installer = $this->installer;

        //-- InternationalReturns
        //########################################

        $installer->getTableModifier('ebay_template_return')
            ->addColumn('international_accepted', 'VARCHAR(255) NOT NULL', NULL,
                        'shipping_cost', false, false)
            ->addColumn('international_option', 'VARCHAR(255) NOT NULL', NULL,
                        'international_accepted', false, false)
            ->addColumn('international_within', 'VARCHAR(255) NOT NULL', NULL,
                        'international_option', false, false)
            ->addColumn('international_shipping_cost', 'VARCHAR(255) NOT NULL', NULL,
                        'international_within', false, false)
            ->dropColumn('holiday_mode', false, false)
            ->dropColumn('restocking_fee', false, false)
            ->commit();

        $installer->getTableModifier('ebay_marketplace')
            ->dropColumn('is_holiday_return', true, false)
            ->addColumn('is_return_description', 'TINYINT(2) UNSIGNED NOT NULL', '0',
                        'is_in_store_pickup', true, false)
            ->commit();

        $installer->run(<<<SQL
UPDATE `m2epro_ebay_template_return`
SET `international_accepted` = 'ReturnsNotAccepted';

UPDATE `m2epro_ebay_marketplace`
SET `is_return_description` = 1
WHERE `marketplace_id` IN (8, 13, 7, 10, 5);
SQL
        );

        //-- WalmartActionProcessorFixes
        //########################################

        $installer->getMainConfigModifier()
            ->insert('/walmart/listing/product/action/processing/prepare/', 'max_listings_products_count', '2000');

        //########################################

        $installer->getMainConfigModifier()
            ->insert('/cron/task/walmart/order/cancel/', 'mode', '1', '0 - disable, \r\n1 - enable');
        $installer->getMainConfigModifier()
            ->insert('/cron/task/walmart/order/cancel/', 'interval', '60', 'in seconds');
        $installer->getMainConfigModifier()
            ->insert('/cron/task/walmart/order/refund/', 'mode', '1', '0 - disable, \r\n1 - enable');
        $installer->getMainConfigModifier()
            ->insert('/cron/task/walmart/order/refund/', 'interval', '60', 'in seconds');

        $installer->getTableModifier('walmart_order_item')
            ->addColumn('status', 'VARCHAR(30) NOT NULL', NULL, 'walmart_order_item_id');
    }

    //########################################
}