<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/** @method Varien_Db_Adapter_Pdo_Mysql getConnection */

class Ess_M2ePro_Model_Upgrade_MySqlSetup extends Mage_Core_Model_Resource_Setup
{
    const MODULE_IDENTIFIER = 'M2ePro_setup';

    const INSTALLATION_HISTORY_KEY = '/installation/versions_history/';

    /**
     * Means that version, upgrade files are included to the build
     */
    const MIN_SUPPORTED_VERSION_FOR_UPGRADE = '5.0.3';

    /** @var string */
    public $versionFrom;

    /** @var string */
    public $versionTo;

    protected $_cache = array();

    //########################################

    protected function _installResourceDb($newVersion)
    {
        if (!$this->getLockObject()->getLock()) {
            return;
        }

        if (Mage::helper('M2ePro/Module_Maintenance')->isEnabled() &&
            !$this->isMaintenanceCanBeIgnored()) {
            return;
        }

        Mage::helper('M2ePro/Module_Maintenance')->enable();

        try {
            $this->beforeModuleDbModification();
            $this->beforeInstall($newVersion);

            /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup_UpgradeManager $upgradeManager */
            $upgradeManager = $this->getUpgradeManager(null, $newVersion);
            $upgradeManager->process();

            $setupObject = $upgradeManager->getCurrentSetupObject();
            $setupObject->setData('is_completed', 1);
            $setupObject->save();

            $this->_setResourceVersion(self::TYPE_DB_INSTALL, $newVersion);

            $this->afterInstall($newVersion);
            $this->afterModuleDbModification();
        } catch (Exception $e) {
            $this->getLockObject()->releaseLock();

            if (isset($setupObject)) {
                $setupObject->setData('profiler_data', $e->__toString());
                $setupObject->save();
            }

            throw $e;
        }

        Mage::helper('M2ePro/Module_Maintenance')->disable();

        $this->getLockObject()->releaseLock();
    }

    protected function _upgradeResourceDb($oldVersion, $newVersion)
    {
        if (!$this->getLockObject()->getLock()) {
            return;
        }

        if (Mage::helper('M2ePro/Module_Maintenance')->isEnabled() &&
            !$this->isMaintenanceCanBeIgnored()) {
            return;
        }

        Mage::helper('M2ePro/Module_Maintenance')->enable();

        try {

            /** @var Ess_M2ePro_Model_Setup[] $notCompletedUpgrades */
            $notCompletedUpgrades = $this->getNotCompletedUpgrades();
            if ($this->getSetupConfigObject()->isAllowedRollbackFromBackup() && !empty($notCompletedUpgrades)) {

                /**
                 * Only one not completed upgrade is supported
                 */
                $notCompletedUpgrade = reset($notCompletedUpgrades);
                if (version_compare($notCompletedUpgrade->getVersionFrom(), $oldVersion, '<')) {
                    $oldVersion = $notCompletedUpgrade->getVersionFrom();
                }
            }

            if (version_compare($oldVersion, self::MIN_SUPPORTED_VERSION_FOR_UPGRADE, '<')) {
                throw new Exception(
                    sprintf(
                        'This version [%s] is too old.', $oldVersion
                    )
                );
            }

            $this->beforeModuleDbModification();
            $this->beforeUpgrade($oldVersion, $newVersion);

            $versionsToExecute = $this->getAvailableFiles($oldVersion, $newVersion);
            foreach ($versionsToExecute as $versionFrom => $versionTo) {

                /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup_UpgradeManager $upgradeManager */
                $upgradeManager = $this->getUpgradeManager($versionFrom, $versionTo);

                $setupObject  = $upgradeManager->getCurrentSetupObject();
                $backupObject = $upgradeManager->getBackupObject();

                if ($setupObject->isBackuped() && $this->getSetupConfigObject()->isAllowedRollbackFromBackup()) {
                    $backupObject->rollback();
                }

                $backupObject->remove();
                $backupObject->create();

                $setupObject->setData('is_backuped', 1);
                $setupObject->save();

                $upgradeManager->process();

                $setupObject->setData('is_completed', 1);
                $setupObject->save();

                $backupObject->remove();

                $this->_setResourceVersion(self::TYPE_DB_UPGRADE, $versionTo);
            }

            $this->afterUpgrade($oldVersion, $newVersion);
            $this->afterModuleDbModification();
        } catch (Exception $e) {
            $this->getLockObject()->releaseLock();

            if (isset($setupObject)) {
                $setupObject->setData('profiler_data', $e->__toString());
                $setupObject->save();
            }

            throw $e;
        }

        Mage::helper('M2ePro/Module_Maintenance')->disable();

        $this->getLockObject()->releaseLock();
    }

    protected function getAvailableFiles($fromVersion, $toVersion)
    {
        $filesDir = Mage::getModuleDir('sql', Ess_M2ePro_Helper_Module::IDENTIFIER) .DS. 'Upgrade';
        if (!is_dir($filesDir) || !is_readable($filesDir)) {
            return array();
        }

        $files = array();
        $directoryIterator = new FilesystemIterator($filesDir, FilesystemIterator::SKIP_DOTS);

        foreach ($directoryIterator as $directory) {
            /**@var SplFileInfo $directory */

            list($fileVersionFrom, $fileVersionTo) = explode('__', $directory->getFilename());
            $fileVersionFrom = str_replace(array('_', 'v'), array('.', ''), $fileVersionFrom);
            $fileVersionTo   = str_replace(array('_', 'v'), array('.', ''), $fileVersionTo);

            if (version_compare($fileVersionFrom, $fromVersion, '<') ||
                version_compare($fileVersionTo, $toVersion, '>')
            ) {
                continue;
            }

            $files[$fileVersionFrom][$fileVersionTo] = $directory->getFilename();
        }

        uksort(
            $files, function($first, $second) {
            return version_compare($first, $second);
            }
        );

        $maxToVersion = null;
        $filesData = array();

        foreach ($files as $fileVersionFrom => $fileData) {
            uksort(
                $fileData, function($first, $second) {
                return version_compare($first, $second);
                }
            );

            end($fileData);
            $finalToVersion = key($fileData);

            if ($maxToVersion !== null && version_compare($finalToVersion, $maxToVersion, '<=')) {
                continue;
            }

            $maxToVersion = $finalToVersion;
            $filesData[$fileVersionFrom] = $finalToVersion;
        }

        return $filesData;
    }

    /**
     * @return Ess_M2ePro_Model_Setup[]
     */
    protected function getNotCompletedUpgrades()
    {
        if (!$this->getConnection()->isTableExists($this->getFullTableName('setup'))) {
            return array();
        }

        $collection = Mage::getModel('M2ePro/Setup')->getCollection();
        $collection->addFieldToFilter('version_from', array('notnull' => true));
        $collection->addFieldToFilter('version_to', array('notnull' => true));
        $collection->addFieldToFilter('is_backuped', 1);
        $collection->addFieldToFilter('is_completed', 0);

        return $collection->getItems();
    }

    //########################################

    public function run($sql)
    {
        if (trim($sql) == '') {
            return $this;
        }

        foreach ($this->getTablesObject()->getAllEntities() as $tableNameFrom => $tableNameTo) {
            $tableNameFrom = ($tableNameFrom == 'ess_config') ?
                $tableNameFrom :
                Ess_M2ePro_Model_Upgrade_Tables::PREFIX . $tableNameFrom;
            $sql = str_replace(' `'.$tableNameFrom.'`', ' `'.$tableNameTo.'`', $sql);
            $sql = str_replace(' '.$tableNameFrom, ' `'.$tableNameTo.'`', $sql);
        }

        return parent::run($sql);
    }

    public function generateRandomHash()
    {
        return sha1(microtime(1));
    }

    //########################################

    protected function beforeModuleDbModification()
    {
        if (extension_loaded('apc') && ini_get('apc.enabled')) {
            apc_clear_cache('system');
        }

        if (function_exists('opcache_get_status')) {
            opcache_reset();
        }

        $this->getConnection()->disallowDdlCache();
    }

    protected function afterModuleDbModification()
    {
        $this->resetServicingStatus();
        $this->removeConfigsDuplicates();

        Mage::helper('M2ePro/Module')->clearCache();

        $this->getConnection()->allowDdlCache();
    }

    // ---------------------------------------

    protected function beforeInstall($newVersion)
    {
        $this->versionFrom = null;
        $this->versionTo   = $newVersion;

        $this->updateInstallationVersionHistory(null, $newVersion);
    }

    protected function afterInstall($newVersion)
    {
        // re init tables after installation
        $this->getTablesObject()->init();
    }

    // ---------------------------------------

    protected function beforeUpgrade($oldVersion, $newVersion)
    {
        $this->versionFrom = $oldVersion;
        $this->versionTo   = $newVersion;

        $this->updateInstallationVersionHistory($oldVersion, $newVersion);
    }

    protected function afterUpgrade($oldVersion, $newVersion)
    {
        try {
            $this->removeOldBackupsTables();
        } catch (Exception $e) {
            Mage::helper('M2ePro/Module_Exception')->process($e);
        }
    }

    //########################################

    protected function updateInstallationVersionHistory($versionFrom, $versionTo)
    {
        if ($versionFrom === $versionTo) {
            return;
        }

        $versionsHistory = (array)$this->getInstallationVersionsHistory();
        $versionsHistory[$versionTo] = array(
            'from' => $versionFrom,
            'to'   => $versionTo,
            'date' => Mage::getModel('core/date')->gmtDate()
        );

        $this->setInstallationVersionsHistory($versionsHistory);
    }

    public function getInstallationVersionsHistory()
    {
        if (!$this->getTablesObject()->isExists('registry')) {
            return null;
        }

        $versionsHistory = $this->getConnection()->select()
            ->from($this->getTablesObject()->getFullName('registry'), array('value'))
            ->where('`key` = ?', self::INSTALLATION_HISTORY_KEY)
            ->query()
            ->fetch();

        if (empty($versionsHistory)) {
            return null;
        }

        return Mage::helper('M2ePro/Data')->jsonDecode($versionsHistory['value']);
    }

    public function setInstallationVersionsHistory($history)
    {
        if (!$this->getTablesObject()->isExists('registry')) {
            return;
        }

        $currentHistory = $this->getInstallationVersionsHistory();
        if ($currentHistory === null) {
            $this->getConnection()->insertArray(
                $this->getTablesObject()->getFullName('registry'),
                array('key', 'value', 'update_date', 'create_date'),
                array(
                    array(
                        'key'         => self::INSTALLATION_HISTORY_KEY,
                        'value'       => Mage::helper('M2ePro/Data')->jsonEncode($history),
                        'update_date' => Mage::getModel('core/date')->gmtDate(),
                        'create_date' => Mage::getModel('core/date')->gmtDate()
                    )
                )
            );

            return;
        }

        $this->getConnection()->update(
            $this->getTablesObject()->getFullName('registry'),
            array(
                'value'       => Mage::helper('M2ePro/Data')->jsonEncode($history),
                'update_date' => Mage::getModel('core/date')->gmtDate(),
            ),
            array('`key` = ?' => self::INSTALLATION_HISTORY_KEY)
        );
    }

    //########################################

    public function startSetup()
    {
        $this->getLockObject()->activateLock();
        return parent::startSetup();
    }

    //########################################

    protected function resetServicingStatus()
    {
        $tableName = 'cache_config';

        if (!$this->getTablesObject()->isExists($tableName)) {
            return;
        }

        $this->getConnection()->update(
            $this->getTablesObject()->getFullName($tableName),
            array('value' => null),
            array(
                '`group` = ?' => '/servicing/',
                '`key` = ?' => 'last_update_time'
            )
        );
    }

    protected function removeOldBackupsTables()
    {
        $versionsHistory = $this->getInstallationVersionsHistory();
        if (empty($versionsHistory)) {
            return;
        }

        $prefixes = array(
            'm2epro__source'      => '6.0.0',
            'ess__source'         => '6.0.0',
            'm2epro__backup_v5'   => '6.0.0',
            'ess__backup_v5'      => '6.0.0',
            'm2epro__backup_v611' => '6.1.1',
            'm2epro__backup_v630' => '6.2.4.3',
            'm2epro__b_65016'     => '6.5.0.16'
        );

        $borderDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $borderDate->modify('- 30 days');

        foreach ($prefixes as $backupPrefix => $removeAfterVersion) {
            $hasToBeRemoved = false;
            foreach ($versionsHistory as $versionHistoryRecord) {
                if (empty($versionHistoryRecord['to']) || empty($versionHistoryRecord['date'])) {
                    continue;
                }

                try {
                    $version          = $versionHistoryRecord['to'];
                    $installationDate = new \DateTime($versionHistoryRecord['date'], new \DateTimeZone('UTC'));
                } catch (\Exception $e) {
                    continue;
                }

                if (version_compare($version, $removeAfterVersion, '>=') &&
                    $installationDate->getTimestamp() < $borderDate->getTimestamp()
                ) {
                    $hasToBeRemoved = true;
                    break;
                }
            }

            if ($hasToBeRemoved) {
                $queryStmt = $this->getConnection()->query("SHOW TABLES LIKE '%{$backupPrefix}%'");

                while ($tableName = $queryStmt->fetchColumn()) {
                    $this->getConnection()->dropTable($tableName);
                }
            }
        }
    }

    // ---------------------------------------

    public function removeConfigsDuplicates()
    {
        if ($this->getTablesObject()->isExists('primary_config')) {
            $this->getConfigModifier('primary_config')->removeDuplicates();
        }

        if ($this->getTablesObject()->isExists('config')) {
            $this->getConfigModifier('config')->removeDuplicates();
        }

        if ($this->getTablesObject()->isExists('cache_config')) {
            $this->getConfigModifier('cache_config')->removeDuplicates();
        }
    }

    //########################################

    /**
     * @return Mage_Core_Model_Resource_Resource
     */
    public function getResource()
    {
        return Mage::getSingleton('core/resource')->getResource();
    }

    public function getFullTableName($tableName)
    {
        return $this->getTablesObject()->getFullName($tableName);
    }

    //########################################

    /**
     * @param string $tableName
     * @return Ess_M2ePro_Model_Upgrade_Modifier_Table
     */
    public function getTableModifier($tableName)
    {
        return $this->getModifier($tableName, 'table');
    }

    /**
     * @param string $tableName
     * @return Ess_M2ePro_Model_Upgrade_Modifier_Config
     */
    public function getConfigModifier($tableName)
    {
        return $this->getModifier($tableName, 'config');
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Upgrade_Modifier_Config
     */
    public function getPrimaryConfigModifier()
    {
        return $this->getModifier('primary_config', 'config');
    }

    /**
     * @return Ess_M2ePro_Model_Upgrade_Modifier_Config
     */
    public function getMainConfigModifier()
    {
        return $this->getModifier('config', 'config');
    }

    /**
     * @return Ess_M2ePro_Model_Upgrade_Modifier_Config
     */
    public function getCacheConfigModifier()
    {
        return $this->getModifier('cache_config', 'config');
    }

    /**
     * @return Ess_M2ePro_Model_Upgrade_Modifier_Config
     */
    public function getSynchConfigModifier()
    {
        return $this->getModifier('synchronization_config', 'config');
    }

    // ---------------------------------------

    /**
     * @param string $tableName
     * @param string $modelName
     * @return Ess_M2ePro_Model_Upgrade_Modifier_Abstract
     */
    protected function getModifier($tableName, $modelName)
    {
        $cacheKey = $tableName . '_' . $modelName;

        if (isset($this->_cache[$cacheKey])) {
            return $this->_cache[$cacheKey];
        }

        /** @var Ess_M2ePro_Model_Upgrade_Modifier_Abstract $object */
        $object = Mage::getModel('M2ePro/Upgrade_Modifier_' . ucfirst($modelName));
        $object->setInstaller($this)->setTableName($tableName);

        return $this->_cache[$cacheKey] = $object;
    }

    //########################################

    /**
     * @param $versionFrom
     * @param $versionTo
     * @return Ess_M2ePro_Model_Upgrade_MySqlSetup_UpgradeManager
     */
    protected function getUpgradeManager($versionFrom, $versionTo)
    {
        $cacheKey = __METHOD__ .'#'. $versionFrom .'#'. $versionTo;

        if (isset($this->_cache[$cacheKey])) {
            return $this->_cache[$cacheKey];
        }

        /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup_UpgradeManager $manager */
        $manager = Mage::getModel(
            'M2ePro/Upgrade_MySqlSetup_UpgradeManager', array(
            $versionFrom, $versionTo, $this
            )
        );

        return $this->_cache[$cacheKey] = $manager;
    }

    /**
     * @return Ess_M2ePro_Model_Upgrade_Tables
     */
    public function getTablesObject()
    {
        if (isset($this->_cache[__METHOD__])) {
            return $this->_cache[__METHOD__];
        }

        /** @var Ess_M2ePro_Model_Upgrade_Tables $object */
        $object = Mage::getModel('M2ePro/Upgrade_Tables', $this);
        return $this->_cache[__METHOD__] = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Upgrade_MySqlSetup_Config
     */
    public function getSetupConfigObject()
    {
        return Mage::getSingleton('M2ePro/Upgrade_MySqlSetup_Config');
    }

    /**
     * @return Ess_M2ePro_Model_Upgrade_MySqlSetup_Lock
     */
    protected function getLockObject()
    {
        return Mage::getSingleton('M2ePro/Upgrade_MySqlSetup_Lock');
    }

    //########################################

    protected function isMaintenanceCanBeIgnored()
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('core_config_data'), 'value')
            ->where('scope = ?', 'default')
            ->where('scope_id = ?', 0)
            ->where('path = ?', 'm2epro/setup/ignore_maintenace');

        return (bool)$this->getConnection()->fetchOne($select);
    }

    //########################################
}
