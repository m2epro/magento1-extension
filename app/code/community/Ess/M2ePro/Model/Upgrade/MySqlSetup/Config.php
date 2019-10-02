<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_MySqlSetup_Config
{
    const ALLOWED_ROLLBACK_FROM_BACKUP_KEY = 'm2epro/setup/allow_rollback_from_backup';

    //########################################

    public function setAllowedRollbackFromBackup($value)
    {
        $this->setMagentoCoreConfigValue(self::ALLOWED_ROLLBACK_FROM_BACKUP_KEY, (int)$value);
    }

    public function isAllowedRollbackFromBackup()
    {
        return (bool)$this->getMagentoCoreConfigValue(self::ALLOWED_ROLLBACK_FROM_BACKUP_KEY);
    }

    //########################################

    protected function getMagentoCoreConfigValue($path)
    {
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_read');

        $select = $connection->select()
            ->from($resource->getTableName('core_config_data'), 'value')
            ->where('scope = ?', 'default')
            ->where('scope_id = ?', 0)
            ->where('path = ?', $path);

        return $connection->fetchOne($select);
    }

    protected function setMagentoCoreConfigValue($path, $value)
    {
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_write');

        if ($this->getMagentoCoreConfigValue($path) === false) {
            $connection->insert(
                $resource->getTableName('core_config_data'),
                array(
                    'scope'    => 'default',
                    'scope_id' => 0,
                    'path'     => $path,
                    'value'    => $value
                )
            );
        } else {
            $connection->update(
                $resource->getTableName('core_config_data'),
                array('value' => $value),
                array(
                    'scope = ?'    => 'default',
                    'scope_id = ?' => 0,
                    'path = ?'     => $path,
                )
            );
        }
    }

    //########################################
}
