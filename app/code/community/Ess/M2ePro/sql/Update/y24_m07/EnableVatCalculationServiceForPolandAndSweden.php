<?php

class Ess_M2ePro_Sql_Update_y24_m07_EnableVatCalculationServiceForPolandAndSweden
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        foreach (array('PL', 'SE') as $countryCode) {
            $marketplaceId = $this->_installer->getConnection()
                ->select()
                ->from($this->_installer->getFullTableName('marketplace'))
                ->where('`code` = ?', $countryCode)
                ->where('`component_mode` = ?', 'amazon')
                ->query()
                ->fetchColumn();

            $this->_installer->getConnection()->update(
                $this->_installer->getFullTableName('amazon_marketplace'),
                array(
                    'is_vat_calculation_service_available' => 1,
                ),
                array(
                    '`marketplace_id` = ?' => $marketplaceId
                )
            );
        }
    }
}