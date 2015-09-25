<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Servicing_Task_Backups_Manager
{
    const GENERAL_SETTINGS_ID = 'general';

    private $availableTables = NULL;

    /** @var Ess_M2ePro_Model_Config_Cache */
    private $cache = NULL;
    private $cacheGroup = '/backup/settings/';

    // ########################################

    public function __construct()
    {
        $this->cache = Mage::helper('M2ePro/Module')->getCacheConfig();
    }

    // ########################################

    public function setSettings(array $settings)
    {
        if (isset($settings['tables']) && is_array($settings['tables'])) {
            foreach ($settings['tables'] as $tableName => $tableSettings) {
                if (!is_array($tableSettings)) {
                    continue;
                }

                foreach ($tableSettings as $tableSettingKey => $tableSettingValue) {
                    $this->setSetting($tableSettingKey, $tableSettingValue, $tableName);
                }
            }
        }

        if (isset($settings[self::GENERAL_SETTINGS_ID]) && is_array($settings[self::GENERAL_SETTINGS_ID])) {
            foreach ($settings[self::GENERAL_SETTINGS_ID] as $generalSettingKey => $generalSettingValue) {
                $this->setSetting($generalSettingKey, $generalSettingValue, self::GENERAL_SETTINGS_ID);
            }
        }

        return $this;
    }

    public function deleteSettings($tableName = NULL)
    {
        $group = $this->prepareSettingGroup($tableName);
        $this->cache->deleteAllGroupValues($group);

        return $this;
    }

    // ########################################

    public function setSetting($key, $value, $tableName = NULL)
    {
        $group = $this->prepareSettingGroup($tableName);
        $this->cache->setGroupValue($group, $key, $value);

        return $this;
    }

    public function getSetting($key, $tableName = NULL)
    {
        $group = $this->prepareSettingGroup($tableName);

        return $this->cache->getGroupValue($group, $key);
    }

    // ########################################

    private function prepareSettingGroup($tableName = NULL)
    {
        $group = $this->cacheGroup;

        if (!is_null($tableName)) {
            $group .= $tableName . '/';
        }

        return $group;
    }

    // ########################################

    public function canBackupTable($tableName)
    {
        if (!in_array($tableName, Mage::helper('M2ePro/Module_Database_Structure')->getMySqlTables())) {
            return false;
        }

        $interval = $this->getSetting('interval', $tableName);

        if (is_null($interval) || (int)$interval <= 0) {
            return false;
        }

        return true;
    }

    public function isTimeToBackupTable($tableName)
    {
        $interval = (int)$this->getSetting('interval', $tableName);

        if ($interval <= 0) {
            return false;
        }

        $currentTimeStamp = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        $lastAccessDate = $this->cache->getGroupValue('/backup/'.$tableName.'/', 'last_access');

        if (!is_null($lastAccessDate) && $currentTimeStamp < strtotime($lastAccessDate) + (int)$interval) {
            return false;
        }

        return true;
    }

    public function updateTableLastAccessDate($tableName)
    {
        $this->cache->setGroupValue(
            '/backup/'.$tableName.'/', 'last_access', Mage::helper('M2ePro')->getCurrentGmtDate()
        );
    }

    // ########################################

    public function getTableDump($tableName, $columns = '*', $count = NULL, $offset = NULL)
    {
        $tableName = Mage::getSingleton('core/resource')->getTableName($tableName);

        if (!in_array($tableName, $this->getAvailableTables())) {
            return array();
        }

        /** @var $connection Varien_Db_Adapter_Pdo_Mysql */
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');

        $select = $connection->select()
            ->from($tableName, $columns)
            ->limit($count, $offset);
        $query = $connection->query($select);

        return $query->fetchAll();
    }

    // ########################################

    private function getAvailableTables()
    {
        if (is_null($this->availableTables)) {
            $this->availableTables = Mage::helper('M2ePro/Magento')->getMySqlTables();
        }
        return $this->availableTables;
    }

    // ########################################
}