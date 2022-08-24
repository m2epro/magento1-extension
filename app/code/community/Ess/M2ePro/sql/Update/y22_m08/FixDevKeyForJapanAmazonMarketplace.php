<?php

class Ess_M2ePro_Sql_Update_y22_m08_FixDevKeyForJapanAmazonMarketplace extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName('amazon_marketplace'),
            array('developer_key' => '2770-5005-3793'),
            array('marketplace_id = ?' => 42)
        );
    }
}
