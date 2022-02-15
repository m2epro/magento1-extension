<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y22_m02_RemoveForumUrl extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $mainConfig = $this->_installer->getMainConfigModifier();

        $mainConfig->delete('/support/', 'forum_url');
    }

    //########################################
}
