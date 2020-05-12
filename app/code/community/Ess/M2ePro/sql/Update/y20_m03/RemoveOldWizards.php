<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m03_RemoveOldWizards extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getConnection()
            ->delete(
                $this->_installer->getFullTableName('wizard'),
                array(
                    '`nick` IN (?)' => array(
                        'migrationNewAmazon',
                        'removedPlay',
                        'fullAmazonCategories',
                        'removedEbay3rdParty',
                        'ebayProductDetails',
                        'removedBuy'
                    )
                )
            );
    }

    //########################################
}
