<?php

class Ess_M2ePro_Sql_Update_y25_m04_AddConditionColumnsIntoWalmartListingTable extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $modifier = $this->_installer->getTableModifier('walmart_listing');
        $modifier->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_Listing::COLUMN_CONDITION_MODE,
            'TINYINT(2) UNSIGNED',
            '0',
            Ess_M2ePro_Model_Resource_Walmart_Listing::COLUMN_TEMPLATE_SYNCHRONIZATION_ID,
            false,
            false
        );
        $modifier->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_Listing::COLUMN_CONDITION_CUSTOM_ATTRIBUTE,
            'VARCHAR(255)',
            'NULL',
            Ess_M2ePro_Model_Resource_Walmart_Listing::COLUMN_CONDITION_MODE,
            false,
            false
        );
        $modifier->addColumn(
            Ess_M2ePro_Model_Resource_Walmart_Listing::COLUMN_CONDITION_VALUE,
            'VARCHAR(255)',
            'NULL',
            Ess_M2ePro_Model_Resource_Walmart_Listing::COLUMN_CONDITION_CUSTOM_ATTRIBUTE,
            false,
            false
        );

        $modifier->commit();
    }
}
