<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y22_m02_ImportTaxRegistrationId extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->_installer
            ->getTableModifier('amazon_order')
            ->addColumn(
                'tax_registration_id',
                'VARCHAR(72)',
                null,
                'ioss_number',
                false,
                false
            )
            ->commit();
    }
}
