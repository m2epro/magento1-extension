<?php

class Ess_M2ePro_Sql_Update_y24_m02_CombineInactiveProductStatuses
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    /**
     * @throws Zend_Db_Adapter_Exception
     */
    public function execute()
    {
        $oldStatuses = array(1, 3, 4);
        $tables = array('listing_product', 'listing_other');

        foreach ($tables as $table) {
            $this->_installer->getConnection()->update(
                $this->_installer->getFullTableName($table),
                array('status' => Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE),
                array('status IN (?)' => $oldStatuses)
            );
        }
    }

}
