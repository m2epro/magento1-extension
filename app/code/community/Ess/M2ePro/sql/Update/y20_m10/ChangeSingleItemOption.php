<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m10_ChangeSingleItemOption extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    private $_tables = array(
        'ebay_account_pickup_store',
        'ebay_template_selling_format',
        'amazon_template_selling_format',
        'walmart_template_selling_format'
    );

    public function execute()
    {
        foreach ($this->_tables as $table) {
            $this->_installer->getConnection()->update(
                $this->_installer->getFullTableName($table),
                array(
                    'qty_mode' => 3,
                    'qty_custom_value' => 1,
                ),
                array(
                    'qty_mode = ?' => 2
                )
            );
        }
    }

    //########################################
}
