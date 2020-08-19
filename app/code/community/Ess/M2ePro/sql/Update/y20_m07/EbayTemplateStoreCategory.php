<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m07_EbayTemplateStoreCategory extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('ebay_template_store_category')
            ->changeColumn('category_id', 'DECIMAL(20, 0) UNSIGNED NOT NULL', null, 'account_id');
    }

    //########################################
}
