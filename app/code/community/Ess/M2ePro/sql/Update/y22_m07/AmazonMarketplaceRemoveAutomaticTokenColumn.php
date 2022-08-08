<?php

class Ess_M2ePro_Sql_Update_y22_m07_AmazonMarketplaceRemoveAutomaticTokenColumn extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->_installer->getTableModifier('amazon_marketplace')
            ->dropColumn('is_automatic_token_retrieving_available', true, true);
    }
}
