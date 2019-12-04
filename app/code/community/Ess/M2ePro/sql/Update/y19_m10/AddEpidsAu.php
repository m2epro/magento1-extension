<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m10_AddEpidsAu extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $configModifier = $this->_installer->getMainConfigModifier();
        $configModifier->insert('/ebay/motors/', 'epids_au_attribute');

        $this->_installer->run(
            <<<SQL
UPDATE `m2epro_ebay_marketplace`
SET `is_epid` = 1
WHERE `marketplace_id` = 4;
SQL
        );
    }

    //########################################
}