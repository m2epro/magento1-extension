<?php

class Ess_M2ePro_Sql_Update_y24_m11_AddDeliveryDateFromColumnToAmazonOrder
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $modifier = $this->_installer->getTableModifier('amazon_order');

        $modifier->addColumn(
            'delivery_date_from',
            'DATETIME',
            null,
            null,
            false,
            false
        );

        $modifier->commit();
    }
}
