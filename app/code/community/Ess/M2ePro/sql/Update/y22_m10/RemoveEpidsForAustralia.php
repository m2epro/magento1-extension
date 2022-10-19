<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y22_m10_RemoveEpidsForAustralia extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    /**
     * @see Ess_M2ePro_Sql_Update_y19_m10_AddEpidsAu
     */
    public function execute()
    {
       $this->removeEpidsFromConfig();
       $this->disableEpidsFromMarketplace();
    }

    private function removeEpidsFromConfig()
    {
        $this->_installer->getMainConfigModifier()
            ->delete('/ebay/configuration/', 'au_epids_attribute');
    }

    private function disableEpidsFromMarketplace()
    {
        $ebayMarketplaceTable = $this->_installer->getFullTableName('ebay_marketplace');
        $sql = "UPDATE `{$ebayMarketplaceTable}` SET `is_epid` = 0 WHERE `marketplace_id` = 4;";
        $this->_installer->run($sql);
    }
}
