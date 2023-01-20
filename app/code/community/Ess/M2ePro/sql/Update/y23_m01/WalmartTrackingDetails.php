<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y23_m01_WalmartTrackingDetails extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->_installer->getTableModifier('walmart_order_item')
            ->addColumn(
                'tracking_details',
                "TEXT",
                null,
                'qty_purchased',
                false,
                false
            )
            ->commit();
    }
}
