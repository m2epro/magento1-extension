<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y22_m11_FixWalmartChildListingId extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $installer = $this->_installer;
        $connection = $installer->getConnection();
        $select = $connection->select();

        $select->join(
            array('wlp' => $installer->getFullTableName('walmart_listing_product')),
            'lp.id = wlp.listing_product_id',
            null
        );
        $select->join(
            array('parent_lp' => $installer->getFullTableName('listing_product')),
            'parent_lp.id = wlp.variation_parent_id',
            array('listing_id' => 'parent_lp.listing_id')
        );
        $select->where('lp.listing_id != parent_lp.listing_id');

        $updateQuery = $connection->updateFromSelect(
            $select,
            array('lp' => $installer->getFullTableName('listing_product'))
        );

        $connection->query($updateQuery);
    }
}
