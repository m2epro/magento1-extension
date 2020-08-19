<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m07_WalmartOrderItemQty extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    /**
     * Magento describeTable works in wrong way when autoCommit = true
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Exception
     */
    public function execute()
    {
        $this->_installer->getTableModifier('walmart_order_item')
            ->changeAndRenameColumn('qty', 'qty_purchased', 'int(11) unsigned not null', '0', 'price', false)
            ->commit();
    }

    //########################################
}
