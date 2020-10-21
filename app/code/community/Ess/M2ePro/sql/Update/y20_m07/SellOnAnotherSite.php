<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m07_SellOnAnotherSite extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getMainConfigModifier()->delete(
            '/ebay/configuration/', 'sell_on_another_marketplace_tutorial_shown'
        );
    }

    //########################################
}
