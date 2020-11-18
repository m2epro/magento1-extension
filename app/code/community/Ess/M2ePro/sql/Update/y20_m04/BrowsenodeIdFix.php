<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m04_BrowsenodeIdFix extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('amazon_dictionary_category_product_data')
            ->changeColumn(
                'browsenode_id',
                'DECIMAL(20, 0) UNSIGNED NOT NULL',
                null,
                'marketplace_id'
            );
    }

    //########################################
}
