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

    /**
     * Means that version, upgrade files are included to the build
     */
    const MIN_SUPPORTED_VERSION_FOR_UPGRADE = '6.0.8';

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

        Mage::helper('M2ePro/Module_Maintenance')->enable();

        try {
            $this->beforeModuleDbModification();
            $this->beforeUpgrade($oldVersion, $newVersion);

            $versionsToExecute = $this->getVersionsToExecute($oldVersion, $newVersion);
            foreach ($versionsToExecute as $versionFrom => $versionTo) {

                /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup_UpgradeManager $upgradeManager */
                $upgradeManager = $this->getUpgradeManager($versionFrom, $versionTo);

                $setupObject  = $upgradeManager->getCurrentSetupObject();
                $backupObject = $upgradeManager->getBackupObject();

                if (!$setupObject->isBackuped()) {
                    $backupObject->create();
                    $setupObject->setData('is_backuped', 1);
                    $setupObject->save();
                }

                $upgradeManager->process();

                $setupObject->setData('is_completed', 1);
                $setupObject->save();

                $backupObject->remove();

                $this->_setResourceVersion(self::TYPE_DB_UPGRADE, $versionTo);
            }

            $this->_setResourceVersion(self::TYPE_DB_UPGRADE, $newVersion);

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

    protected function getVersionsToExecute($versionFrom, $versionTo)
    {
        /** @var Ess_M2ePro_Model_Setup[] $notCompletedUpgrades */
        $notCompletedUpgrades = Mage::getModel('M2ePro/Setup')->getResource()
            ->getNotCompletedUpgrades();

        if (!empty($notCompletedUpgrades)) {
            /**
             * Only one not completed upgrade is supported
             */
            $notCompletedUpgrade = reset($notCompletedUpgrades);
            if (version_compare($notCompletedUpgrade->getVersionFrom(), $versionFrom, '<')) {
                $versionFrom = $notCompletedUpgrade->getVersionFrom();
            }
        }

        if (version_compare($versionFrom, self::MIN_SUPPORTED_VERSION_FOR_UPGRADE, '<')) {
            // @codingStandardsIgnoreLine
            throw new Exception(sprintf('This version [%s] is too old.', $versionFrom));
        }

        return $this->getAvailableFiles($versionFrom, $versionTo);
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

    //########################################

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

        if (function_exists('opcache_reset')) {
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
    }

    // ---------------------------------------

    protected function beforeUpgrade($oldVersion, $newVersion)
    {
        $this->versionFrom = $oldVersion;
        $this->versionTo   = $newVersion;
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

    public function startSetup()
    {
        $this->getLockObject()->activateLock();
        return parent::startSetup();
    }

    //########################################

    protected function resetServicingStatus()
    {
        $tableName = 'registry';

        if (!$this->getTablesObject()->isExists($tableName)) {
            return;
        }

        $this->getConnection()->update(
            $this->getTablesObject()->getFullName($tableName),
            array('value' => null),
            array('`key` = ?' => '/servicing/last_update_time/')
        );
    }

    protected function removeOldBackupsTables()
    {
        $prefixes = array(
            'm2epro__source'      => '6.0.0',
            'ess__source'         => '6.0.0',
            'm2epro__backup_v5'   => '6.0.0',
            'ess__backup_v5'      => '6.0.0',
            'm2epro__backup_v611' => '6.1.1',
            'm2epro__backup_v630' => '6.2.4.3',
            'm2epro__b_65016'     => '6.5.0.16'
        );

        foreach ($prefixes as $backupPrefix => $removeAfterVersion) {
            if (version_compare($this->versionTo, $removeAfterVersion, '<')) {
                continue;
            }

            $queryStmt = $this->getConnection()->query("SHOW TABLES LIKE '%{$backupPrefix}%'");
            while ($tableName = $queryStmt->fetchColumn()) {
                $this->getConnection()->dropTable($tableName);
            }
        }
    }

    // ---------------------------------------

    public function removeConfigsDuplicates()
    {
        if ($this->getTablesObject()->isExists('config')) {
            $this->getConfigModifier('config')->removeDuplicates();
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
     * @deprecated
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
     * @deprecated
     * @return Ess_M2ePro_Model_Upgrade_Modifier_Config
     */
    public function getCacheConfigModifier()
    {
        return $this->getModifier('cache_config', 'config');
    }

    /**
     * @deprecated
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
     * @return Ess_M2ePro_Model_Upgrade_MySqlSetup_Lock
     */
    protected function getLockObject()
    {
        return Mage::getSingleton('M2ePro/Upgrade_MySqlSetup_Lock');
    }

    //########################################
}
