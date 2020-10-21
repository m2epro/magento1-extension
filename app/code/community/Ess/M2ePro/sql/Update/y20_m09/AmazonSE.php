<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m09_AmazonSE extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $marketplace = $this->_installer->getConnection()->select()
            ->from($this->_installer->getFullTableName('marketplace'))
            ->where('id = ?', 41)
            ->query()
            ->fetch();

        if ($marketplace === false) {
            $this->_installer->getConnection()->insert(
                $this->_installer->getFullTableName('marketplace'),
                array(
                    'id'             => 41,
                    'native_id'      => 13,
                    'title'          => 'Sweden',
                    'code'           => 'SE',
                    'url'            => 'amazon.se',
                    'status'         => 0,
                    'sorder'         => 15,
                    'group_title'    => 'Europe',
                    'component_mode' => 'amazon',
                    'update_date'    => '2020-09-03 00:00:00',
                    'create_date'    => '2020-09-03 00:00:00'
                )
            );
        }

        $marketplace = $this->_installer->getConnection()->select()
            ->from($this->_installer->getFullTableName('amazon_marketplace'))
            ->where('marketplace_id = ?', 41)
            ->query()
            ->fetch();

        if ($marketplace === false) {
            $this->_installer->getConnection()->insert(
                $this->_installer->getFullTableName('amazon_marketplace'),
                array(
                    'marketplace_id'                          => 41,
                    'developer_key'                           => '7078-7205-1944',
                    'default_currency'                        => 'SEK',
                    'is_new_asin_available'                   => 1,
                    'is_merchant_fulfillment_available'       => 1,
                    'is_business_available'                   => 0,
                    'is_vat_calculation_service_available'    => 0,
                    'is_product_tax_code_policy_available'    => 0,
                    'is_automatic_token_retrieving_available' => 1,
                    'is_upload_invoices_available'            => 0
                )
            );
        }
    }

    //########################################
}