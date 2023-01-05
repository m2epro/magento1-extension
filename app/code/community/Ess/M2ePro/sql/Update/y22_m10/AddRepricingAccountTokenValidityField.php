<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y22_m10_AddRepricingAccountTokenValidityField
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    /**
     * @throws Zend_Db_Adapter_Exception
     */
    public function execute()
    {
        $this->_installer->getTableModifier('amazon_account_repricing')
            ->addColumn(
                'invalid',
                'SMALLINT UNSIGNED NOT NULL',
                0,
                'token',
                false,
                false
            )
            ->commit();
    }
}
