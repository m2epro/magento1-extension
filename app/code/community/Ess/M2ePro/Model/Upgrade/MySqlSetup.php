<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Upgrade_MySqlSetup extends Mage_Core_Model_Resource_Setup
{
    private $cache = array();

    //####################################

    public function endSetup()
    {
        $this->resetServicingData();
        $this->removeConfigDuplicates();
        Mage::helper('M2ePro/Module')->clearCache();
        return parent::endSetup();
    }

    // ----------------------------------

    protected function _upgradeResourceDb($oldVersion, $newVersion)
    {
        parent::_upgradeResourceDb($oldVersion, $newVersion);

        $this->updateInstallationVersionHistory($oldVersion, $newVersion);
        $this->updateCompilation();

        return $this;
    }

    protected function _installResourceDb($newVersion)
    {
        parent::_installResourceDb($newVersion);

        $this->updateInstallationVersionHistory(null, $newVersion);
        $this->updateCompilation();

        return $this;
    }

    //####################################

    public function run($sql)
    {
        if (trim($sql) == '') {
            return $this;
        }

        foreach ($this->getTablesObject()->getAllHistoryEntities() as $tableFrom => $tableTo) {
            $sql = str_replace(' `'.$tableFrom.'`',' `'.$tableTo.'`',$sql);
            $sql = str_replace(' '.$tableFrom,' `'.$tableTo.'`',$sql);
        }

        return parent::run($sql);
    }

    //####################################

    /**
     * @return Ess_M2ePro_Model_Upgrade_Tables
     */
    public function getTablesObject()
    {
        $cacheKey = 'tablesObject';
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        /** @var Ess_M2ePro_Model_Upgrade_Tables $tablesObjectModel */
        $tablesObjectModel = Mage::getModel('M2ePro/Upgrade_Tables');
        $tablesObjectModel->setInstaller($this);

        $this->cache[$cacheKey] = $tablesObjectModel;
        return $tablesObjectModel;
    }

    /**
     * @param $tableName
     * @return Ess_M2ePro_Model_Upgrade_Modifier_Table
     */
    public function getTableModifier($tableName)
    {
        return $this->getModifier($tableName, 'table');
    }

    /**
     * @param $tableName
     * @return Ess_M2ePro_Model_Upgrade_Modifier_Config
     */
    // TODO NEXT (rename getConfigModifier)
    public function getConfigUpdater($tableName)
    {
        return $this->getModifier($tableName, 'config');
    }

    /**
     * @param $tableName
     * @param $modifierModelName
     * @return Ess_M2ePro_Model_Upgrade_Modifier_Abstract
     */
    private function getModifier($tableName, $modifierModelName)
    {
        $cacheKey = $tableName . '_' . $modifierModelName;
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        /** @var Ess_M2ePro_Model_Upgrade_Modifier_Abstract $tableModifier */
        $tableModifier = Mage::getModel('M2ePro/Upgrade_Modifier_' . ucfirst($modifierModelName));
        $tableModifier->setInstaller($this);
        $tableModifier->setConnection($this->getConnection());
        $tableModifier->setTableName($tableName);

        $this->cache[$cacheKey] = $tableModifier;
        return $tableModifier;
    }

    //####################################

    public function generateRandomHash()
    {
        return sha1(microtime(1));
    }

    public function removeConfigDuplicates()
    {
        $tables = $this->getTablesObject()->getAllHistoryConfigEntities();
        $connection = $this->getConnection();

        foreach ($tables as $tableName) {

            // TODO NEXT (move to config modifier)
            if (!in_array($tableName, $connection->listTables())) {
                return;
            }

            $configRows = $connection->query("SELECT `id`, `group`, `key`
                                              FROM `{$tableName}`
                                              ORDER BY `id` ASC")
                                     ->fetchAll();

            $tempData = array();
            $deleteData = array();

            foreach ($configRows as $configRow) {

                $tempName = strtolower($configRow['group'] .'|'. $configRow['key']);

                if (in_array($tempName, $tempData)) {
                    $deleteData[] = (int)$configRow['id'];
                } else {
                    $tempData[] = $tempName;
                }
            }

            if (!empty($deleteData)) {
                $connection->query("DELETE FROM `{$tableName}`
                                    WHERE `id` IN (".implode(',', $deleteData).')');
            }
        }
    }

    //####################################

    private function updateInstallationVersionHistory($oldVersion, $newVersion)
    {
        $connection = $this->getConnection();
        $tableName = $this->getTable('m2epro_registry');

        if (!in_array($tableName, $connection->listTables())) {
            return;
        }

        $currentGmtDate = Mage::getModel('core/date')->gmtDate();
        $versionsHistory = $connection->select()
                                      ->from($tableName, array('key', 'value'))
                                      ->where('`key` = ?', '/installation/versions_history/')
                                      ->query()
                                      ->fetch();
        $versionData = array(
            'from' => $oldVersion,
            'to'   => $newVersion,
            'date' => $currentGmtDate
        );

        if (!empty($versionsHistory)) {

            $versionsHistory = @json_decode($versionsHistory['value'], true);
            $versionsHistory[] = $versionData;
            $mysqlData = array(
                'value'       => @json_encode($versionsHistory),
                'update_date' => $currentGmtDate,
                'create_date' => $currentGmtDate
            );

            $connection->update($tableName, $mysqlData, array('`key` = ?' => '/installation/versions_history/'));
        } else {

            $mysqlData = array(
                'key'         => '/installation/versions_history/',
                'value'       => @json_encode(array($versionData)),
                'update_date' => $currentGmtDate,
                'create_date' => $currentGmtDate
            );
            $mysqlColumns = array('key','value','update_date','create_date');

            $connection->insertArray($tableName, $mysqlColumns, array($mysqlData));
        }
    }

    private function updateCompilation()
    {
        defined('COMPILER_INCLUDE_PATH') && Mage::getModel('compiler/process')->run();
    }

    private function resetServicingData()
    {
        $connection = $this->getConnection();
        $tableName = $this->getTable('m2epro_cache_config');

        $connection->update($tableName, array('value' => NULL),
            array(
                '`group` = ?' => '/servicing/',
                '`key` = ?' => 'last_update_time'
            )
        );
    }

    //####################################
}