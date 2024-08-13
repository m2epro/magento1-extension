<?php

class Ess_M2ePro_Sql_Update_y24_m08_AddDateOfInvoiceSendingToAmazonOrder
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->_installer
            ->getTableModifier('amazon_order')
            ->addColumn(
                Ess_M2ePro_Model_Resource_Amazon_Order::COLUMN_DATE_OF_INVOICE_SENDING,
                'DATETIME',
                'NULL'
            );
    }
}
