<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y22_m06_WalmartOrderItemBuyerCancellation extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->_installer->getTableModifier('walmart_order_item')
            ->addColumn(
                'buyer_cancellation_requested',
                'SMALLINT(4) UNSIGNED NOT NULL',
                0,
                'qty_purchased',
                false,
                false
            )
            ->commit();
    }
}
