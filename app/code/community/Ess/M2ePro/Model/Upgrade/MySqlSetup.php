<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_MySqlSetup extends Mage_Core_Model_Resource_Setup
{
    const LOCK_FILE_LIFETIME = 300;

    private $lockId;
    private $cache = array();

    //########################################

    /**
     * @return Ess_M2ePro_Model_Upgrade_Tables
     */
    public function getTablesObject()
    {
        $cacheKey = 'tablesObject';

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        /** @var Ess_M2ePro_Model_Upgrade_Tables $object */
        $object = Mage::getModel('M2ePro/Upgrade_Tables');
        $object->setInstaller($this)->initialize();

        return $this->cache[$cacheKey] = $object;
    }

    // ---------------------------------------

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
    private function getModifier($tableName, $modelName)
    {
        $cacheKey = $tableName . '_' . $modelName;

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        /** @var Ess_M2ePro_Model_Upgrade_Modifier_Abstract $object */
        $object = Mage::getModel('M2ePro/Upgrade_Modifier_' . ucfirst($modelName));
        $object->setInstaller($this)->setTableName($tableName);

        return $this->cache[$cacheKey] = $object;
    }

    //########################################

    public function run($sql)
    {
        if (trim($sql) == '') {
            return $this;
        }

        foreach ($this->getTablesObject()->getAllHistoryEntities() as $tableNameFrom => $tableNameTo) {
            $tableNameFrom = ($tableNameFrom == 'ess_config') ?
                                $tableNameFrom :
                                Ess_M2ePro_Model_Upgrade_Tables::M2E_PRO_TABLE_PREFIX . $tableNameFrom;
            $sql = str_replace(' `'.$tableNameFrom.'`',' `'.$tableNameTo.'`',$sql);
            $sql = str_replace(' '.$tableNameFrom,' `'.$tableNameTo.'`',$sql);
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
    }

    protected function afterModuleDbModification()
    {
        $this->resetServicingStatus();
        $this->removeConfigsDuplicates();

        Mage::helper('M2ePro/Module')->clearCache();
    }

    // ---------------------------------------

    protected function beforeInstall($newVersion)
    {
        $this->updateInstallationVersionHistory(null, $newVersion);
    }

    protected function afterInstall($newVersion) {}

    // ---------------------------------------

    protected function beforeUpgrade($oldVersion, $newVersion)
    {
        $this->updateInstallationVersionHistory($oldVersion, $newVersion);
    }

    protected function afterUpgrade($oldVersion, $newVersion) {}

    // ---------------------------------------

    protected function beforeFileExecution() {}

    protected function afterFileExecution() {}

    //########################################

    protected function _installResourceDb($newVersion)
    {
        // double running protection
        usleep(1000000); // 1 sec

        if ($this->isLocked()) {
            return;
        }

        $this->lock();

        try {

            $this->beforeModuleDbModification();
            $this->beforeInstall($newVersion);

            parent::_installResourceDb($newVersion);

            $this->afterInstall($newVersion);
            $this->afterModuleDbModification();

        } catch (Exception $e) {

            $this->unlock();
            throw $e;
        }

        $this->unlock();
    }

    protected function _upgradeResourceDb($oldVersion, $newVersion)
    {
        // double running protection
        usleep(1000000); // 1 sec

        if ($this->isLocked()) {
            return;
        }

        $this->lock();

        try {

            $this->beforeModuleDbModification();
            $this->beforeUpgrade($oldVersion, $newVersion);

            parent::_upgradeResourceDb($oldVersion, $newVersion);

            $this->afterUpgrade($oldVersion, $newVersion);
            $this->afterModuleDbModification();

        } catch (Exception $e) {

            $this->unlock();
            throw $e;
        }

        $this->unlock();
    }

    // ---------------------------------------

    public function startSetup()
    {
        $this->lock();

        $this->beforeFileExecution();
        return parent::startSetup();
    }

    public function endSetup()
    {
        parent::endSetup();
        $this->afterFileExecution();

        if ($this->isLockFileExists() &&
            @file_get_contents($this->getLockFilePath()) != $this->lockId) {
            exit();
        }

        return $this;
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
            array('value' => NULL),
            array(
                '`group` = ?' => '/servicing/',
                '`key` = ?' => 'last_update_time'
            )
        );
    }

    public function removeConfigsDuplicates()
    {
        foreach ($this->getTablesObject()->getAllHistoryConfigEntities() as $tableName => $tableFullName) {

            if (!$this->getTablesObject()->isExists($tableName)) {
                continue;
            }

            $this->getConfigModifier($tableName)->removeDuplicates();
        }
    }

    protected function updateInstallationVersionHistory($oldVersion, $newVersion)
    {
        $tableName = 'registry';

        if (!$this->getTablesObject()->isExists($tableName)) {
            return;
        }

        $currentGmtDate = Mage::getModel('core/date')->gmtDate();
        $fullTableName = $this->getTablesObject()->getFullName($tableName);

        $versionData = array(
            'from' => $oldVersion,
            'to'   => $newVersion,
            'date' => $currentGmtDate
        );

        $versionsHistory = $this->getConnection()->select()
                                                 ->from($fullTableName, array('key', 'value'))
                                                 ->where('`key` = ?', '/installation/versions_history/')
                                                 ->query()
                                                 ->fetch();
        if (!empty($versionsHistory)) {

            $versionsHistory = @json_decode($versionsHistory['value'], true);
            $versionsHistory[] = $versionData;

            $mysqlData = array(
                'value'       => @json_encode($versionsHistory),
                'update_date' => $currentGmtDate,
                'create_date' => $currentGmtDate
            );

            $this->getConnection()
                 ->update($fullTableName, $mysqlData, array('`key` = ?' => '/installation/versions_history/'));

        } else {

            $mysqlData = array(
                'key'         => '/installation/versions_history/',
                'value'       => @json_encode(array($versionData)),
                'update_date' => $currentGmtDate,
                'create_date' => $currentGmtDate
            );
            $mysqlColumns = array('key','value','update_date','create_date');

            $this->getConnection()->insertArray($fullTableName, $mysqlColumns, array($mysqlData));
        }
    }

    //########################################

    private function getLocksDirPath()
    {
        return Mage::getBaseDir('var') . DS . 'locks';
    }

    private function getLockFilePath()
    {
        return rtrim($this->getLocksDirPath(), DS) . DS . 'm2epro_setup.lock';
    }

    private function isLockFileExists()
    {
        return @file_exists($this->getLockFilePath());
    }

    // ---------------------------------------

    private function isLocked()
    {
        if (!$this->isLockFileExists()) {
            return false;
        }

        if (@filemtime($this->getLockFilePath()) > ((int)gmdate('U') - self::LOCK_FILE_LIFETIME)) {
            return true;
        }

        $this->unlock();
        return false;
    }

    private function lock()
    {
        $this->lockId = $this->generateRandomHash();

        if (!@is_dir($this->getLocksDirPath())) {
            @mkdir($this->getLocksDirPath(), 0777, true);
        }

        @file_put_contents($this->getLockFilePath(), $this->lockId);

        register_shutdown_function(function () {
            @unlink(Mage::getBaseDir('var').DS.'locks'.DS.'m2epro_setup.lock');
        });
    }

    private function unlock()
    {
        $this->isLockFileExists() && @unlink($this->getLockFilePath());
    }

    //########################################
}