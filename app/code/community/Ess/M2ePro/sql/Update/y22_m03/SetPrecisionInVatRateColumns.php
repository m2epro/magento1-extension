<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y22_m03_SetPrecisionInVatRateColumns extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('ebay_template_selling_format')
            ->changeColumn(
                'vat_percent',
                'decimal(10,2) unsigned not null',
                '0',
                null,
                false
            )
            ->commit();

        $this->_installer->getTableModifier('amazon_template_selling_format')
            ->changeColumn(
                'regular_price_vat_percent',
                'decimal(10,2) unsigned',
                null,
                null,
                false
            )
            ->changeColumn(
                'business_price_vat_percent',
                'decimal(10,2) unsigned',
                null,
                null,
                false
            )
            ->commit();

        $this->_installer->getTableModifier('walmart_template_selling_format')
            ->changeColumn(
                'price_vat_percent',
                'decimal(10,2) unsigned',
                null,
                null,
                false
            )
            ->commit();
    }

    //########################################
}
