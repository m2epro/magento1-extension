<?php

class Ess_M2ePro_Sql_Update_y25_m04_AddIntoWalmartProductIsNotMappedToExistingChannelItemColumn extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $modifier = $this->_installer->getTableModifier('walmart_listing_product');
        $modifier->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_Listing_Product::COLUMN_IS_NOT_MAPPED_TO_EXISTING_CHANNEL_ITEM,
            'TINYINT(2) UNSIGNED NOT NULL',
            '0',
            null,
            false,
            false
        );
        $modifier->commit();
    }
}

