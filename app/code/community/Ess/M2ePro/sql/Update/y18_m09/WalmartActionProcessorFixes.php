<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y18_m09_WalmartActionProcessorFixes extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer
            ->getMainConfigModifier()
            ->insert('/walmart/listing/product/action/processing/prepare/', 'max_listings_products_count', '2000');
    }

    //########################################
}
