<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m01_WalmartRemoveChannelUrl extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('walmart_listing_other')->dropColumn('channel_url');
        $this->_installer->getTableModifier('walmart_listing_product')->dropColumn('channel_url');
    }

    //########################################
}
