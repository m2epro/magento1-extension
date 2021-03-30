<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y21_m01_EbayRemoveClickAndCollect extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('ebay_marketplace')
            ->dropColumn('is_click_and_collect', true, false)
            ->commit();

        $this->_installer->getTableModifier('ebay_template_shipping')
            ->dropColumn('click_and_collect_mode', false, false)
            ->commit();

        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName('ebay_template_shipping_calculated'),
            array(
                'package_size_mode'  => 0,
                'package_size_value' => ''
            ),
            array('package_size_value = ?' => 'None')
        );
    }

    //########################################
}
