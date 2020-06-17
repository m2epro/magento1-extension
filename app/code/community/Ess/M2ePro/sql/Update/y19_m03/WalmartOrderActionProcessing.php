<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m03_WalmartOrderActionProcessing
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->run(
            "DROP TABLE IF EXISTS `{$this->_installer->getTable('m2epro_walmart_order_action_processing')}`"
        );
    }

    //########################################
}