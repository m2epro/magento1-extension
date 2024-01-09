<?php

class Ess_M2ePro_Sql_Update_y23_m12_RemoveAmazonSearchByMagentoTitleMode extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $modifier = $this->_installer->getTableModifier('m2epro_amazon_listing');
        $modifier->dropColumn('search_by_magento_title_mode');
    }
}
