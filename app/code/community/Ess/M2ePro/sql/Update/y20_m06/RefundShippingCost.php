<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m06_RefundShippingCost extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer
            ->getTableModifier('amazon_order_item')
            ->addColumn('shipping_price', 'DECIMAL(12, 4) UNSIGNED NOT NULL', 0.0000, 'price');
    }

    //########################################
}