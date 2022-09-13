<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y22_m09_AddAmazonMarketplaceBelgium extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $marketplace = $this->_installer->getConnection()->select()
            ->from($this->_installer->getFullTableName('marketplace'))
            ->where('id = ?', 48)
            ->query()
            ->fetch();

        if ($marketplace === false) {
            $this->_installer->getConnection()->insert(
                $this->_installer->getFullTableName('marketplace'),
                array(
                    'id'             => 48,
                    'native_id'      => 20,
                    'title'          => 'Belgium',
                    'code'           => 'BE',
                    'url'            => 'amazon.com.be',
                    'status'         => 0,
                    'sorder'         => 22,
                    'group_title'    => 'Europe',
                    'component_mode' => 'amazon',
                    'update_date'    => '2022-09-01 00:00:00',
                    'create_date'    => '2022-09-01 00:00:00'
                )
            );
        }

        $marketplace = $this->_installer->getConnection()->select()
            ->from($this->_installer->getFullTableName('amazon_marketplace'))
            ->where('marketplace_id = ?', 48)
            ->query()
            ->fetch();

        if ($marketplace === false) {
            $this->_installer->getConnection()->insert(
                $this->_installer->getFullTableName('amazon_marketplace'),
                array(
                    'marketplace_id'                          => 48,
                    'developer_key'                           => '7078-7205-1944',
                    'default_currency'                        => 'EUR',
                    'is_new_asin_available'                   => 0,
                    'is_merchant_fulfillment_available'       => 1,
                    'is_business_available'                   => 1,
                    'is_vat_calculation_service_available'    => 0,
                    'is_product_tax_code_policy_available'    => 0,
                )
            );
        }
    }
}