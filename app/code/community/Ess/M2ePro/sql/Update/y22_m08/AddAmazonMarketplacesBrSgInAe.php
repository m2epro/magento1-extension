<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y22_m08_AddAmazonMarketplacesBrSgInAe extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->installBrazil();
        $this->installSingapore();
        $this->installIndia();
        $this->installUnitedArabEmirates();
    }

    private function installBrazil()
    {
        $marketplace = $this->_installer->getConnection()->select()
            ->from($this->_installer->getFullTableName('marketplace'))
            ->where('id = ?', 44)
            ->query()
            ->fetch();

        if ($marketplace === false) {
            $this->_installer->getConnection()->insert(
                $this->_installer->getFullTableName('marketplace'),
                array(
                    'id'             => 44,
                    'native_id'      => 16,
                    'title'          => 'Brazil',
                    'code'           => 'BR',
                    'url'            => 'amazon.com.br',
                    'status'         => 0,
                    'sorder'         => 18,
                    'group_title'    => 'America',
                    'component_mode' => 'amazon',
                    'update_date'    => '2022-08-15 00:00:00',
                    'create_date'    => '2022-08-15 00:00:00'
                )
            );
        }

        $marketplace = $this->_installer->getConnection()->select()
            ->from($this->_installer->getFullTableName('amazon_marketplace'))
            ->where('marketplace_id = ?', 44)
            ->query()
            ->fetch();

        if ($marketplace === false) {
            $this->_installer->getConnection()->insert(
                $this->_installer->getFullTableName('amazon_marketplace'),
                array(
                    'marketplace_id'                          => 44,
                    'developer_key'                           => '8636-1433-4377',
                    'default_currency'                        => 'BRL',
                    'is_new_asin_available'                   => 0,
                    'is_merchant_fulfillment_available'       => 1,
                    'is_business_available'                   => 1,
                    'is_vat_calculation_service_available'    => 0,
                    'is_product_tax_code_policy_available'    => 0,
                )
            );
        }
    }

    private function installSingapore()
    {
        $marketplace = $this->_installer->getConnection()->select()
            ->from($this->_installer->getFullTableName('marketplace'))
            ->where('id = ?', 45)
            ->query()
            ->fetch();

        if ($marketplace === false) {
            $this->_installer->getConnection()->insert(
                $this->_installer->getFullTableName('marketplace'),
                array(
                    'id'             => 45,
                    'native_id'      => 17,
                    'title'          => 'Singapore',
                    'code'           => 'SG',
                    'url'            => 'amazon.sg',
                    'status'         => 0,
                    'sorder'         => 19,
                    'group_title'    => 'Asia / Pacific',
                    'component_mode' => 'amazon',
                    'update_date'    => '2022-08-15 00:00:00',
                    'create_date'    => '2022-08-15 00:00:00'
                )
            );
        }

        $marketplace = $this->_installer->getConnection()->select()
            ->from($this->_installer->getFullTableName('amazon_marketplace'))
            ->where('marketplace_id = ?', 45)
            ->query()
            ->fetch();

        if ($marketplace === false) {
            $this->_installer->getConnection()->insert(
                $this->_installer->getFullTableName('amazon_marketplace'),
                array(
                    'marketplace_id'                          => 45,
                    'developer_key'                           => '2770-5005-3793',
                    'default_currency'                        => 'SGD',
                    'is_new_asin_available'                   => 0,
                    'is_merchant_fulfillment_available'       => 1,
                    'is_business_available'                   => 1,
                    'is_vat_calculation_service_available'    => 0,
                    'is_product_tax_code_policy_available'    => 0,
                )
            );
        }
    }

    private function installIndia()
    {
        $marketplace = $this->_installer->getConnection()->select()
            ->from($this->_installer->getFullTableName('marketplace'))
            ->where('id = ?', 46)
            ->query()
            ->fetch();

        if ($marketplace === false) {
            $this->_installer->getConnection()->insert(
                $this->_installer->getFullTableName('marketplace'),
                array(
                    'id'             => 46,
                    'native_id'      => 18,
                    'title'          => 'India',
                    'code'           => 'IN',
                    'url'            => 'amazon.in',
                    'status'         => 0,
                    'sorder'         => 20,
                    'group_title'    => 'Europe',
                    'component_mode' => 'amazon',
                    'update_date'    => '2022-08-15 00:00:00',
                    'create_date'    => '2022-08-15 00:00:00'
                )
            );
        }

        $marketplace = $this->_installer->getConnection()->select()
            ->from($this->_installer->getFullTableName('amazon_marketplace'))
            ->where('marketplace_id = ?', 46)
            ->query()
            ->fetch();

        if ($marketplace === false) {
            $this->_installer->getConnection()->insert(
                $this->_installer->getFullTableName('amazon_marketplace'),
                array(
                    'marketplace_id'                          => 46,
                    'developer_key'                           => '7078-7205-1944',
                    'default_currency'                        => 'INR',
                    'is_new_asin_available'                   => 0,
                    'is_merchant_fulfillment_available'       => 1,
                    'is_business_available'                   => 1,
                    'is_vat_calculation_service_available'    => 0,
                    'is_product_tax_code_policy_available'    => 0,
                )
            );
        }
    }

    private function installUnitedArabEmirates()
    {
        $marketplace = $this->_installer->getConnection()->select()
            ->from($this->_installer->getFullTableName('marketplace'))
            ->where('id = ?', 47)
            ->query()
            ->fetch();

        if ($marketplace === false) {
            $this->_installer->getConnection()->insert(
                $this->_installer->getFullTableName('marketplace'),
                array(
                    'id'             => 47,
                    'native_id'      => 19,
                    'title'          => 'United Arab Emirates',
                    'code'           => 'AE',
                    'url'            => 'amazon.ae',
                    'status'         => 0,
                    'sorder'         => 21,
                    'group_title'    => 'Europe',
                    'component_mode' => 'amazon',
                    'update_date'    => '2022-08-15 00:00:00',
                    'create_date'    => '2022-08-15 00:00:00'
                )
            );
        }

        $marketplace = $this->_installer->getConnection()->select()
            ->from($this->_installer->getFullTableName('amazon_marketplace'))
            ->where('marketplace_id = ?', 47)
            ->query()
            ->fetch();

        if ($marketplace === false) {
            $this->_installer->getConnection()->insert(
                $this->_installer->getFullTableName('amazon_marketplace'),
                array(
                    'marketplace_id'                          => 47,
                    'developer_key'                           => '7078-7205-1944',
                    'default_currency'                        => 'AED',
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