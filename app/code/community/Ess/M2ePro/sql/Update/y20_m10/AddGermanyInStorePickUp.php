<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m10_AddGermanyInStorePickUp extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName('ebay_marketplace'),
            array(
                'is_in_store_pickup' => 1,
            ),
            array(
                'marketplace_id IN (?)' => array(2, 8)
            )
        );
    }

    //########################################
}
