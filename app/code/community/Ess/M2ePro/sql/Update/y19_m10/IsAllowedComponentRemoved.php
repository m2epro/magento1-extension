<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m10_IsAllowedComponentRemoved
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getMainConfigModifier()->delete('/component/ebay/', 'allowed');
        $this->_installer->getMainConfigModifier()->delete('/component/amazon/', 'allowed');
        $this->_installer->getMainConfigModifier()->delete('/component/walmart/', 'allowed');
    }

    //########################################
}
