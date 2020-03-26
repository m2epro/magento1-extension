<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m11_WalmartProductIdOverride extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getMainConfigModifier()->insert(
            '/walmart/configuration/', 'product_id_override_mode', '0'
        );
    }

    //########################################
}