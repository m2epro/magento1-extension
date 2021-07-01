<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y21_m06_EbayTaxReference extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('ebay_order')
            ->addColumn('tax_reference', 'VARCHAR(72) DEFAULT NULL', null, 'tax_details');
    }

    //########################################
}
