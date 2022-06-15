<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Update_y22_m06_FixMistakenConfigs extends
    Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    public function execute()
    {
        $configKeys = array('is_disabled', 'environment');
        foreach ($configKeys as $configKey) {
            $this->fixMistakenConfig($configKey);
        }
    }

    private function fixMistakenConfig($configKey)
    {
        $entity = $this->_installer->getMainConfigModifier()->getEntity('//', $configKey);

        if ($entity->getValue() === null) {
            return;
        }

        $updated = $this->_installer->getMainConfigModifier()->updateValue($entity->getValue(), array(
                '`group` = ?' => '/',
                '`key` = ?' => $configKey
            )
        );

        if ($updated > 0) {
            $entity->delete();
        }
    }
}
