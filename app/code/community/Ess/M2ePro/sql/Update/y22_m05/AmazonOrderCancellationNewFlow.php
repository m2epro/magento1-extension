<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y22_m05_AmazonOrderCancellationNewFlow extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('amazon_order')
            ->addColumn(
                'is_buyer_requested_cancel',
                'SMALLINT(5) UNSIGNED NOT NULL',
                0,
                'tax_registration_id',
                false,
                false
            )
            ->addColumn(
                'buyer_cancel_reason',
                'TEXT',
                null,
                'is_buyer_requested_cancel',
                false,
                false
            )
            ->commit();
    }

    //########################################
}
