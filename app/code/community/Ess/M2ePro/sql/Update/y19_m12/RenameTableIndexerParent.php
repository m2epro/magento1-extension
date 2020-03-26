<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m12_RenameTableIndexerParent
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTablesObject()->renameTable(
            'ebay_indexer_listing_product_parent',
            'ebay_listing_product_indexer_variation_parent'
        );

        $this->_installer->getTablesObject()->renameTable(
            'amazon_indexer_listing_product_parent',
            'amazon_listing_product_indexer_variation_parent'
        );

        $this->_installer->getTablesObject()->renameTable(
            'walmart_indexer_listing_product_parent',
            'walmart_listing_product_indexer_variation_parent'
        );
    }

    //########################################
}
