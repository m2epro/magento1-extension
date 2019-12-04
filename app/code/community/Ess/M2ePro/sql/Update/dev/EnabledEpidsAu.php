<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_Dev_EnabledEpidsAu extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
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