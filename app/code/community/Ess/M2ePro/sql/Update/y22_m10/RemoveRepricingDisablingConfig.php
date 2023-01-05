<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y22_m10_RemoveRepricingDisablingConfig extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    /**
     * @return void
     * @throws Ess_M2ePro_Model_Exception_Setup
     */
    public function execute()
    {
        $this->_installer->getMainConfigModifier()
            ->delete('/amazon/repricing/', 'mode');
    }
}
