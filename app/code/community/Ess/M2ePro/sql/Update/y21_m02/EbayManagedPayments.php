<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y21_m02_EbayManagedPayments extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName('ebay_marketplace'),
            array('is_managed_payments' => 1),
            array('marketplace_id IN (?)' => array(7, 10, 13))
        );
    }

    //########################################
}
