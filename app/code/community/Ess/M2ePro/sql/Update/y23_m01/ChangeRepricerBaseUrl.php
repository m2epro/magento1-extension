<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y23_m01_ChangeRepricerBaseUrl extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->_installer->getConnection()->update(
            $this->_installer->getFullTableName('config'),
            array(
                'value' => 'https://repricer.m2e.cloud/connector/m2epro/',
            ),
            array(
                "`group` = '/amazon/repricing/' AND `key` = 'base_url'"
            )
        );
    }
}
