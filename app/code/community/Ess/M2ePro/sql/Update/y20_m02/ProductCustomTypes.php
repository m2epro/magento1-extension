<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m02_ProductCustomTypes extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getMainConfigModifier()->insert('/magento/product/simple_type/', 'custom_types', '');
        $this->_installer->getMainConfigModifier()->insert('/magento/product/downloadable_type/', 'custom_types', '');
        $this->_installer->getMainConfigModifier()->insert('/magento/product/configurable_type/', 'custom_types', '');
        $this->_installer->getMainConfigModifier()->insert('/magento/product/bundle_type/', 'custom_types', '');
        $this->_installer->getMainConfigModifier()->insert('/magento/product/grouped_type/', 'custom_types', '');
    }

    //########################################
}