<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y24_m12_AddAmazonMarketplaceIreland extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $irelandMarketplaceId = 51;

        $marketplace = $this->_installer->getConnection()->select()
            ->from($this->_installer->getFullTableName('marketplace'))
            ->where('id = ?', $irelandMarketplaceId)
            ->query()
            ->fetch();

        if ($marketplace === false) {
            $this->_installer->getConnection()->insert(
                $this->_installer->getFullTableName('marketplace'),
                array(
                    'id'             => $irelandMarketplaceId,
                    'native_id'      => 23,
                    'title'          => 'Ireland',
                    'code'           => 'IE',
                    'url'            => 'amazon.ie',
                    'status'         => 0,
                    'sorder'         => 24,
                    'group_title'    => 'Europe',
                    'component_mode' => 'amazon',
                    'update_date'    => '2024-12-20 00:00:00',
                    'create_date'    => '2024-12-20 00:00:00'
                )
            );
        }

        $marketplace = $this->_installer->getConnection()->select()
            ->from($this->_installer->getFullTableName('amazon_marketplace'))
            ->where('marketplace_id = ?', $irelandMarketplaceId)
            ->query()
            ->fetch();

        if ($marketplace === false) {
            $this->_installer->getConnection()->insert(
                $this->_installer->getFullTableName('amazon_marketplace'),
                array(
                    'marketplace_id'                          => $irelandMarketplaceId,
                    'default_currency'                        => 'EUR',
                    'is_merchant_fulfillment_available'       => 1,
                    'is_business_available'                   => 1,
                    'is_vat_calculation_service_available'    => 1,
                    'is_product_tax_code_policy_available'    => 0,
                )
            );
        }
    }
}
