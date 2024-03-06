<?php

class Ess_M2ePro_Sql_Update_y24_m02_CombineInactiveEbayProductStatuses
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    /**
     * @throws Zend_Db_Adapter_Exception
     */
    public function execute()
    {
        $oldStatuses = array(
            Ess_M2ePro_Model_Listing_Product::STATUS_SOLD,
            Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED,
            Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED
        );
        $tables = array('listing_product', 'listing_other', 'ebay_listing_product_variation');

        foreach ($tables as $table) {
            $conditions = array(
                'status IN (?)' => $oldStatuses,
            );

            if ($table === 'listing_product' || $table === 'listing_other') {
                $conditions['component_mode = ?'] = 'ebay';
            }

            $this->_installer->getConnection()->update(
                $this->_installer->getFullTableName($table),
                array('status' => Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE),
                $conditions
            );
        }
    }

}
