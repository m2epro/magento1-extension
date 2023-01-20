<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y23_m01_RemoveConfigConvertLinebreaks extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $config = $this->_installer->getMainConfigModifier();

        $config->delete(
            '/general/configuration/',
            'renderer_description_convert_linebreaks_mode'
        );
    }
}
