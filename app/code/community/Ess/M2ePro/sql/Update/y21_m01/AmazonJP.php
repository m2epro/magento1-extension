<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y21_m01_AmazonJP extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $marketplace = $this->_installer->getConnection()->select()
            ->from($this->_installer->getFullTableName('marketplace'))
            ->where('id = ?', 42)
            ->query()
            ->fetch();

        if ($marketplace === false) {
            $this->_installer->getConnection()->insert(
                $this->_installer->getFullTableName('marketplace'),
                array(
                    'id'             => 42,
                    'native_id'      => 14,
                    'title'          => 'Japan',
                    'code'           => 'JP',
                    'url'            => 'amazon.co.jp',
                    'status'         => 0,
                    'sorder'         => 16,
                    'group_title'    => 'Asia / Pacific',
                    'component_mode' => 'amazon',
                    'update_date'    => '2021-01-11 00:00:00',
                    'create_date'    => '2021-01-11 00:00:00'
                )
            );
        }

        $marketplace = $this->_installer->getConnection()->select()
            ->from($this->_installer->getFullTableName('amazon_marketplace'))
            ->where('marketplace_id = ?', 42)
            ->query()
            ->fetch();

        if ($marketplace === false) {
            $this->_installer->getConnection()->insert(
                $this->_installer->getFullTableName('amazon_marketplace'),
                array(
                    'marketplace_id'                          => 42,
                    'developer_key'                           => '7078-7205-1944',
                    'default_currency'                        => 'JPY',
                    'is_new_asin_available'                   => 0,
                    'is_merchant_fulfillment_available'       => 1,
                    'is_business_available'                   => 0,
                    'is_vat_calculation_service_available'    => 0,
                    'is_product_tax_code_policy_available'    => 0,
                    'is_automatic_token_retrieving_available' => 1
                )
            );
        }
    }

    //########################################
}