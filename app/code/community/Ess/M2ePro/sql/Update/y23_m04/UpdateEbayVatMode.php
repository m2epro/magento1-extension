<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y23_m04_UpdateEbayVatMode extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName('ebay_template_selling_format'),
            array('vat_mode' => '2'),
            array(
                '`vat_mode` = ?' => '1',
                '`price_increase_vat_percent` = ?' => '1'
            )
        );

        $this->_installer->getTableModifier('ebay_template_selling_format')
            ->dropColumn('price_increase_vat_percent', true, false)
            ->commit();
    }
}
