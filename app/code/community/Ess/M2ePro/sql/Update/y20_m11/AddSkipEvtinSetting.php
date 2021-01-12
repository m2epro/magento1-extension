<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m11_AddSkipEvtinSetting extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('ebay_account')->addColumn(
            'skip_evtin',
            'TINYINT(2) UNSIGNED NOT NULL',
            0,
            'create_magento_shipment',
            false
        );
    }

    //########################################
}
