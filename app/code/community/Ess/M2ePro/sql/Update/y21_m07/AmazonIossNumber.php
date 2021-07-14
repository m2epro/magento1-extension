<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y21_m07_AmazonIossNumber extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('amazon_order')
            ->addColumn('ioss_number', 'VARCHAR(72) DEFAULT NULL', null, 'tax_details');
    }

    //########################################
}