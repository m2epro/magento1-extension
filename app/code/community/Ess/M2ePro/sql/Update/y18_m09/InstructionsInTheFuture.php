<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y18_m09_InstructionsInTheFuture extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('listing_product_instruction')
                         ->addColumn('skip_until', 'DATETIME', 'NULL', 'additional_data', true, false)
                         ->commit();
    }

    //########################################
}
