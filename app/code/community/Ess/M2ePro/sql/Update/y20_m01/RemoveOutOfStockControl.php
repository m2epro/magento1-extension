<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m01_RemoveOutOfStockControl extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('ebay_template_selling_format')
            ->dropColumn('out_of_stock_control');

        $this->_installer->getConnection()
            ->update(
                $this->_installer->getFullTableName('ebay_template_selling_format'),
                array('duration_mode' => 100), // Ess_M2ePro_Helper_Component_Ebay::LISTING_DURATION_GTC
                array('listing_type = ?' => 2) // Ess_M2ePro_Model_Ebay_Template_SellingFormat::LISTING_TYPE_FIXED
            );
    }

    //########################################
}