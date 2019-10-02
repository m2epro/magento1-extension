<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Module_Maintenance extends Mage_Core_Helper_Abstract
{
    const CONFIG_PATH = 'm2epro/maintenance';
    const MENU_ROOT_NODE_NICK = 'm2epro_maintenance';

    //########################################

    public function isEnabled()
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $select = $connRead->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('core_config_data'), 'value'
            )
            ->where('scope = ?', 'default')
            ->where('scope_id = ?', 0)
            ->where('path = ?', self::CONFIG_PATH);

        return (bool)$connRead->fetchOne($select);
    }

    //########################################

    public function enable()
    {
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $select = $connWrite->select()
            ->from(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('core_config_data'), 'value'
            )
            ->where('scope = ?', 'default')
            ->where('scope_id = ?', 0)
            ->where('path = ?', self::CONFIG_PATH);

        if ($connWrite->fetchOne($select) === false) {
            $connWrite->insert(
                Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('core_config_data'),
                array(
                    'scope' => 'default',
                    'scope_id' => 0,
                    'path' => self::CONFIG_PATH,
                    'value' => 1
                )
            );
            return;
        }

        $connWrite->update(
            Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('core_config_data'),
            array('value' => 1),
            array(
                'scope = ?' => 'default',
                'scope_id = ?' => 0,
                'path = ?' => self::CONFIG_PATH,
            )
        );
    }

    public function disable()
    {
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $connWrite->update(
            Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('core_config_data'),
            array('value' => 0),
            array(
                'scope = ?' => 'default',
                'scope_id = ?' => 0,
                'path = ?' => self::CONFIG_PATH,
            )
        );
    }

    //########################################
}
