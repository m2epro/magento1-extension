<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m08_EbayShippingSurcharge extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('ebay_template_shipping_service')->dropColumn('cost_surcharge_value');
    }

    //########################################
}
