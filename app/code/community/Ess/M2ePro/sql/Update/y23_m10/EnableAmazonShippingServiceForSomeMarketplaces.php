<?php

class Ess_M2ePro_Sql_Update_y23_m10_EnableAmazonShippingServiceForSomeMarketplaces extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $marketplaces = array(
            array('name' => 'Canada', 'id' => 24),
            array('name' => 'United States', 'id' => 29),
            array('name' => 'Mexico', 'id' => 34),
            array('name' => 'Spain', 'id' => 30),
            array('name' => 'United Kingdom', 'id' => 28),
            array('name' => 'France', 'id' => 26),
            array('name' => 'Germany', 'id' => 25),
            array('name' => 'Italy', 'id' => 31),
            array('name' => 'India', 'id' => 46),
        );

        $ids = array();
        foreach ($marketplaces as $row) {
            $ids[] = $row['id'];
        }

        $this->_installer->getConnection()->update(
            $this->_installer->getTablesObject()->getFullName('m2epro_amazon_marketplace'),
            array('is_merchant_fulfillment_available' => 1),
            array('marketplace_id IN (?)' => $ids)
        );
    }
}
