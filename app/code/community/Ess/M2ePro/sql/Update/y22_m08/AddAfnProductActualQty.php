<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y22_m08_AddAfnProductActualQty extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->_installer->getTableModifier('amazon_listing_product')
            ->addColumn(
                'online_afn_qty',
                'INT(11) UNSIGNED',
                null,
                'online_qty',
                false,
                false
            )
            ->commit();

        $this->_installer->getTableModifier('amazon_listing_other')
            ->addColumn(
                'online_afn_qty',
                'INT(11) UNSIGNED',
                null,
                'online_qty',
                false,
                false
            )
            ->commit();
    }
}
