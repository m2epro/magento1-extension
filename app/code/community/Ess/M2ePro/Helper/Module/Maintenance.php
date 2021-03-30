<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Module_Maintenance extends Mage_Core_Helper_Abstract
{
    const MAINTENANCE_CONFIG_PATH = 'm2epro/maintenance';
    const MENU_ROOT_NODE_NICK = 'm2epro_maintenance';

    private $cache = array();

    //########################################

    public function isEnabled()
    {
        return (bool)$this->getConfig(self::MAINTENANCE_CONFIG_PATH);
    }

    public function enable()
    {
        $this->setConfig(self::MAINTENANCE_CONFIG_PATH, 1);
    }

    public function disable()
    {
        $this->setConfig(self::MAINTENANCE_CONFIG_PATH, 0);
    }

    //########################################

    protected function getConfig($path)
    {
        if (isset($this->cache[$path])) {
            return $this->cache[$path];
        }

        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $select = $connRead
            ->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('core_config_data'),
                'value'
            )
            ->where('scope = ?', 'default')
            ->where('scope_id = ?', 0)
            ->where('path = ?', $path);

        return $this->cache[$path] = $connRead->fetchOne($select);
    }

    protected function setConfig($path, $value)
    {
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        if ($this->getConfig($path) === false) {
            $connWrite->insert(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('core_config_data'),
                array(
                    'scope'    => 'default',
                    'scope_id' => 0,
                    'path'     => $path,
                    'value'    => $value
                )
            );
        } else {
            $connWrite->update(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('core_config_data'),
                array('value' => $value),
                array(
                    'scope = ?'    => 'default',
                    'scope_id = ?' => 0,
                    'path = ?'     => $path,
                )
            );
        }

        unset($this->cache[$path]);
    }

    //########################################
}
