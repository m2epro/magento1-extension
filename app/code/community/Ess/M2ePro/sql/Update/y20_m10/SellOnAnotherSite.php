<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m10_SellOnAnotherSite extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('amazon_listing')->addColumn(
            'product_add_ids',
            'TEXT',
            'NULL',
            'restock_date_custom_attribute'
        );
    }

    //########################################
}
