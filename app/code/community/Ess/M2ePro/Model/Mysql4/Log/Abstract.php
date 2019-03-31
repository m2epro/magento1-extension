<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Mysql4_Log_Abstract
    extends Ess_M2ePro_Model_Mysql4_Abstract
{
    const ACTION_KEY = 'last_action_id';

    //########################################

    public function getLastActionIdConfigKey()
    {
        return 'general';
    }

    public function getNextActionId()
    {
        $groupConfig = '/logs/'.$this->getLastActionIdConfigKey().'/';
        $configTableName = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_config');

        $actionId = $this->getReadConnection()->select()
            ->from($configTableName,'value')
            ->where('`group` = ?', $groupConfig)
            ->where('`key` = ?',self::ACTION_KEY)
            ->query()->fetchColumn();

        $actionId++;

        $this->_getWriteAdapter()->update(
            $configTableName,
            array('value' => $actionId),
            array('`group` = ?' => $groupConfig, '`key` = ?' => self::ACTION_KEY)
        );

        return (int)$actionId;
    }

    //########################################

    public function clearMessages($filters = array())
    {
        $where = array();
        foreach ($filters as $column => $value) {
            $where[$column.' = ?'] = $value;
        }

        $this->_getWriteAdapter()->delete($this->getMainTable(), $where);
    }

    //########################################
}