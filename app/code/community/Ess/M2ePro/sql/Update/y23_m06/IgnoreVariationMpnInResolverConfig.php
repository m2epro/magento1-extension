<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y23_m06_IgnoreVariationMpnInResolverConfig extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $moduleConfigModifier = $this->_installer->getMainConfigModifier();
        $moduleConfigModifier->insert(
            '/ebay/configuration/',
            'ignore_variation_mpn_in_resolver',
            '0'
        );
    }
}
