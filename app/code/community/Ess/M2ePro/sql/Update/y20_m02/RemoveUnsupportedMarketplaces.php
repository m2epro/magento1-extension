<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m02_RemoveUnsupportedMarketplaces
    extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getConnection()->delete(
            $this->_installer->getFullTableName('marketplace'),
            array('id IN (?)' => array(27, 32, 36)) // Japan, China, India
        );

        $this->_installer->getConnection()->delete(
            $this->_installer->getFullTableName('amazon_marketplace'),
            array('marketplace_id IN (?)' => array(27, 32, 36)) // Japan, China, India
        );
    }

    //########################################
}
