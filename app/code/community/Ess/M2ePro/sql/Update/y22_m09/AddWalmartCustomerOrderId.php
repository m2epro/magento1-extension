<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y22_m09_AddWalmartCustomerOrderId extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->_installer
            ->getTableModifier('walmart_order')
            ->addColumn(
                'customer_order_id',
                'VARCHAR(255) NOT NULL',
                '',
                'walmart_order_id',
                true,
                false
            )
            ->commit();
    }
}
