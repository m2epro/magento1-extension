<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y23_m04_SetIsVatEbayMarketplacePL extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $ebayMarketplaceTable = $this->_installer->getFullTableName('ebay_marketplace');
        $this->_installer->getConnection()->update(
            $ebayMarketplaceTable,
            array('is_vat' => 1),
            'marketplace_id = 21'
        );
    }
}
