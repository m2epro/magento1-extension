<?php

class Ess_M2ePro_Sql_Update_y22_m08_FixNullableGroupsInConfigs extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $this->_installer->getMainConfigModifier()->updateGroup('/', array(
                "`key` IN ('is_disabled', 'environment')",
                '`group` IS NULL'
            )
        );
    }
}
