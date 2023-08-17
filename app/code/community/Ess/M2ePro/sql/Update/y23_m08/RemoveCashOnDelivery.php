<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y23_m08_RemoveCashOnDelivery extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->_installer->getTableModifier('ebay_marketplace')
            ->dropColumn('is_cash_on_delivery');
        $this->_installer->getTableModifier('ebay_template_shipping')
            ->dropColumn('cash_on_delivery_cost');
    }
}