<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y25_m03_AddAmazonOriginalOrderIdColumn extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $modifier = $this->_installer->getTableModifier('amazon_order');

        $modifier->addColumn(
            'replaced_amazon_order_id',
            'VARCHAR(255)',
            null,
            null,
            true,
            false
        );

        $modifier->commit();
    }
}
