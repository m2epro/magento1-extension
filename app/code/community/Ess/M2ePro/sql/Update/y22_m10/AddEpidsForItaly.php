<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y22_m10_AddEpidsForItaly extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->_installer
            ->getMainConfigModifier()
            ->insert('/ebay/configuration/', 'it_epids_attribute');

        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName('ebay_marketplace'),
            array(
                'is_epid' => 1,
            ),
            array(
                'marketplace_id = ?' => 10
            )
        );
    }
}
