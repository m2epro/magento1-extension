<?php

class Ess_M2ePro_Sql_Update_y24_m09_AddInternationalShippingRateTablesForAustralia
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName('m2epro_ebay_marketplace'),
            array(
                'is_international_shipping_rate_table' => 1,
            ),
            array(
                '`marketplace_id` = ?' => 4
            )
        );
    }
}
