<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y21_m02_MoveAUtoAsiaPacific extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $marketplaces = $this->_installer->getConnection()->select()
            ->from($this->_installer->getFullTableName('marketplace'))
            ->where('group_title = ?', 'Australia Region')
            ->query();

        while ($row = $marketplaces->fetch()) {
            $this->_installer->getConnection()->update(
                $this->_installer->getFullTableName('marketplace'),
                array('group_title' => 'Asia / Pacific'),
                array('id = ?' => (int)$row['id'])
            );;
        }
    }

    //########################################
}