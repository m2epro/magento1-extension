<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y24_m12_AddAmazonMarketplaceSaudiArabia extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $saudiArabiaMarketplaceId = 50;

        $marketplace = $this->_installer->getConnection()->select()
            ->from($this->_installer->getFullTableName('marketplace'))
            ->where('id = ?', $saudiArabiaMarketplaceId)
            ->query()
            ->fetch();

        if ($marketplace === false) {
            $this->_installer->getConnection()->insert(
                $this->_installer->getFullTableName('marketplace'),
                array(
                    'id'             => $saudiArabiaMarketplaceId,
                    'native_id'      => 22,
                    'title'          => 'Saudi Arabia',
                    'code'           => 'SA',
                    'url'            => 'amazon.sa',
                    'status'         => 0,
                    'sorder'         => 23,
                    'group_title'    => 'Europe',
                    'component_mode' => 'amazon',
                    'update_date'    => '2023-06-25 00:00:00',
                    'create_date'    => '2023-06-25 00:00:00'
                )
            );
        }

        $marketplace = $this->_installer->getConnection()->select()
            ->from($this->_installer->getFullTableName('amazon_marketplace'))
            ->where('marketplace_id = ?', $saudiArabiaMarketplaceId)
            ->query()
            ->fetch();

        if ($marketplace === false) {
            $this->_installer->getConnection()->insert(
                $this->_installer->getFullTableName('amazon_marketplace'),
                array(
                    'marketplace_id'                          => $saudiArabiaMarketplaceId,
                    'default_currency'                        => 'SAR',
                    'is_merchant_fulfillment_available'       => 1,
                    'is_business_available'                   => 1,
                    'is_vat_calculation_service_available'    => 1,
                    'is_product_tax_code_policy_available'    => 0,
                )
            );
        }
    }
}
