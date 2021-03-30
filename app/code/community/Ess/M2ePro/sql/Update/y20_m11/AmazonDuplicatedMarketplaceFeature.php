<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m11_AmazonDuplicatedMarketplaceFeature extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('amazon_marketplace')
            ->dropColumn('is_upload_invoices_available', true, false)
            ->commit();
    }

    //########################################
}
