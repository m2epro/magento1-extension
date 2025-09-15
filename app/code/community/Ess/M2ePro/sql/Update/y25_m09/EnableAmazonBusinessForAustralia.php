<?php

class Ess_M2ePro_Sql_Update_y25_m09_EnableAmazonBusinessForAustralia extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $b2bMarketplaces = array(
            Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_US,
            Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_CA,
            Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_MX,
            Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_UK,
            Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_FR,
            Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_DE,
            Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_IT,
            Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_ES,
            Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_AU,
            Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_JP,
            Ess_M2ePro_Helper_Component_Amazon::MARKETPLACE_IN,
        );

        $amazonMarketplaceTableName = $this->_installer->getFullTableName('m2epro_amazon_marketplace');

        $this->_installer->getConnection()->update(
            $amazonMarketplaceTableName,
            array('is_business_available' => 0)
        );

        $this->_installer->getConnection()->update(
            $amazonMarketplaceTableName,
            array('is_business_available' => 1),
            sprintf('marketplace_id IN (%s)', implode(',', $b2bMarketplaces))
        );
    }
}
