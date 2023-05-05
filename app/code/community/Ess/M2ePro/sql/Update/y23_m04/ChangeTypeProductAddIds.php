<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y23_m04_ChangeTypeProductAddIds extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->_installer->getTableModifier('ebay_listing')
            ->changeColumn('product_add_ids', 'LONGTEXT', 'NULL', null, false)
            ->commit();

        $this->_installer->getTableModifier('amazon_listing')
            ->changeColumn('product_add_ids', 'LONGTEXT', 'NULL', null, false)
            ->commit();
    }
}
