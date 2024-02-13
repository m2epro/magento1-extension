<?php

class Ess_M2ePro_Sql_Update_y24_m02_AddListPriceToSellingPolicy extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $modifier = $this->_installer->getTableModifier('amazon_template_selling_format');
        $modifier->addColumn(
            'regular_list_price_mode',
            'SMALLINT UNSIGNED NOT NULL',
            0,
            'regular_price_variation_mode',
            false,
            false
        );
        $modifier->addColumn(
            'regular_list_price_custom_attribute',
            'VARCHAR(255)',
            'NULL',
            'regular_list_price_mode',
            false,
            false
        );
        $modifier->commit();
    }
}
