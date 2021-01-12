<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m10_AddShipmentToAmazonListing extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        if (!$this->_installer->getTableModifier('amazon_listing')->isColumnExists('template_shipping_id')) {
            $this->_installer->getTableModifier('amazon_listing')
                ->addColumn(
                    'template_shipping_id',
                    'INT(11) UNSIGNED DEFAULT NULL',
                    null,
                    'template_synchronization_id',
                    true
                );
        }
    }

    //########################################
}