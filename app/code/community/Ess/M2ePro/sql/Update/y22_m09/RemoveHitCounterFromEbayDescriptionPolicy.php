<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y22_m09_RemoveHitCounterFromEbayDescriptionPolicy extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->_installer->getTableModifier('ebay_template_description')
            ->dropColumn('hit_counter');
    }
}
