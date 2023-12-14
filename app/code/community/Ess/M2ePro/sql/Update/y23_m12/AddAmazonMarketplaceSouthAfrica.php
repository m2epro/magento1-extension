<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y23_m12_AddAmazonMarketplaceSouthAfrica extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $marketplace = $this->_installer->getConnection()->select()
            ->from($this->_installer->getFullTableName('marketplace'))
            ->where('id = ?', 49)
            ->query()
            ->fetch();

        if ($marketplace === false) {
            $this->_installer->getConnection()->insert(
                $this->_installer->getFullTableName('marketplace'),
                array(
                    'id'             => 49,
                    'native_id'      => 21,
                    'title'          => 'South Africa',
                    'code'           => 'ZA',
                    'url'            => 'amazon.co.za',
                    'status'         => 0,
                    'sorder'         => 23,
                    'group_title'    => 'Europe',
                    'component_mode' => 'amazon',
                    'update_date'    => '2023-12-14 00:00:00',
                    'create_date'    => '2023-12-14 00:00:00'
                )
            );
        }

        $marketplace = $this->_installer->getConnection()->select()
            ->from($this->_installer->getFullTableName('amazon_marketplace'))
            ->where('marketplace_id = ?', 49)
            ->query()
            ->fetch();

        if ($marketplace === false) {
            $this->_installer->getConnection()->insert(
                $this->_installer->getFullTableName('amazon_marketplace'),
                array(
                    'marketplace_id'                          => 49,
                    'default_currency'                        => 'ZAR',
                    'is_new_asin_available'                   => 1,
                    'is_merchant_fulfillment_available'       => 1,
                    'is_business_available'                   => 1,
                    'is_vat_calculation_service_available'    => 1,
                    'is_product_tax_code_policy_available'    => 1,
                )
            );
        }
    }
}