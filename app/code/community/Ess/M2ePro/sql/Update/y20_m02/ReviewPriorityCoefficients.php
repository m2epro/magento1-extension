<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y20_m02_ReviewPriorityCoefficients extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $connection = $this->_installer->getConnection();
        $connection->delete($this->_installer->getFullTableName('config'), array(
            '`key` IN (?)' => array(
                'wait_increase_coefficient',
                'priority_coefficient'
            )
        ));
    }

    //########################################
}