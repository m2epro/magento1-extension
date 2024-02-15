<?php

class Ess_M2ePro_Sql_Update_y24_m02_ChangeTypeCustomValueFieldOnCategorySpecificTable
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    /**
     * @throws Ess_M2ePro_Model_Exception_Setup
     * @throws Zend_Db_Exception
     */
    public function execute()
    {
        $this->_installer->getTableModifier('ebay_template_category_specific')
            ->changeColumn(
                'value_custom_value',
                'TEXT'
            )
            ->commit();
    }
}
