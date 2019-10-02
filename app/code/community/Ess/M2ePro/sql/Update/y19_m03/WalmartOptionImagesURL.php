<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y19_m03_WalmartOptionImagesURL extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->getMainConfigModifier()
                         ->insert('/walmart/configuration/', 'option_images_url_mode', '0');
    }

    //########################################
}