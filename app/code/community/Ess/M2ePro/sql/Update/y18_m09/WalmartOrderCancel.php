<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y18_m09_WalmartOrderCancel extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getMainConfigModifier()
                         ->insert('/cron/task/walmart/order/cancel/', 'mode', '1', '0 - disable, \r\n1 - enable');

        $this->_installer->getMainConfigModifier()
                         ->insert('/cron/task/walmart/order/cancel/', 'interval', '60', 'in seconds');

        $this->_installer->getMainConfigModifier()
                         ->insert('/cron/task/walmart/order/refund/', 'mode', '1', '0 - disable, \r\n1 - enable');

        $this->_installer->getMainConfigModifier()
                         ->insert('/cron/task/walmart/order/refund/', 'interval', '60', 'in seconds');

        $this->_installer->getTableModifier('walmart_order_item')
                         ->addColumn('status', 'VARCHAR(30) NOT NULL', NULL, 'walmart_order_item_id');
    }

    //########################################
}
