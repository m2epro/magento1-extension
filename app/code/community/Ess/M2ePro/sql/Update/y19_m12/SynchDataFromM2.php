<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m12_SynchDataFromM2
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getTableModifier('amazon_marketplace')
            ->addIndex('is_automatic_token_retrieving_available');

        $this->_installer->getTableModifier('amazon_dictionary_category')
            ->changeColumn('product_data_nicks', 'TEXT', 'NULL', null, false)
            ->changeColumn('path', 'TEXT', 'NULL', null, false)
            ->commit();

        $this->_installer->getTableModifier('amazon_listing')
            ->changeColumn('condition_note_value', 'TEXT NOT NULL', null, null, true);

        $this->_installer->getTableModifier('amazon_order_item')
            ->changeColumn('gift_message', 'TEXT', 'NULL', null, true);

        $this->_installer->getTableModifier('ebay_dictionary_category')
            ->changeColumn('path', 'TEXT', 'NULL', null, true);

        $this->_installer->getTableModifier('ebay_order')
            ->changeColumn('buyer_message', 'TEXT', 'NULL', null, true);

        $this->_installer->getTableModifier('ebay_template_selling_format')
            ->changeColumn('charity', 'TEXT', 'NULL', null, true);

        $this->_installer->getTableModifier('ebay_template_shipping_calculated')
            ->changeColumn('package_size_value', 'TEXT NOT NULL', null, null, false)
            ->changeColumn('dimension_width_value', 'TEXT NOT NULL', null, null, false)
            ->changeColumn('dimension_length_value', 'TEXT NOT NULL', null, null, false)
            ->changeColumn('dimension_depth_value', 'TEXT NOT NULL', null, null, false)
            ->changeColumn('weight_minor', 'TEXT NOT NULL', null, null, false)
            ->changeColumn('weight_major', 'TEXT NOT NULL', null, null, false)
            ->commit();

        $this->_installer->getTableModifier('walmart_account')
            ->changeColumn('client_id', 'VARCHAR(255)', 'NULL', null, true);

        $this->_installer->getTableModifier('walmart_dictionary_category')
            ->changeColumn('product_data_nicks', 'TEXT', 'NULL', null, false)
            ->changeColumn('path', 'TEXT', 'NULL', null, false)
            ->commit();

        $this->_installer->getTableModifier('walmart_order_item')
            ->changeColumn('merged_walmart_order_item_ids', 'TEXT', 'NULL', null, true);
    }

    //########################################
}
