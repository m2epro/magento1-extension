<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m12_RemoveReviseTotal
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function getBackupTables()
    {
        return array(
            'config'
        );
    }

    public function execute()
    {
        $this->_installer->getMainConfigModifier()->delete('/view/synchronization/revise_total/');
        $this->_installer->getMainConfigModifier()->delete('/listing/product/revise/total/ebay/');
        $this->_installer->getMainConfigModifier()->delete('/listing/product/revise/total/amazon/');
        $this->_installer->getMainConfigModifier()->delete('/listing/product/revise/total/walmart/');
    }

    //########################################
}
