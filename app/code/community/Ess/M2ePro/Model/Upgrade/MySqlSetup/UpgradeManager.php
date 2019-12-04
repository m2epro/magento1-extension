<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_MySqlSetup_UpgradeManager
{
    /** @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    protected $_installer;

    protected $_versionFrom;
    protected $_versionTo;

    /** @var Ess_M2ePro_Model_Upgrade_Backup */
    protected $_backupObject;

    /** @var Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig */
    protected $_configObject;

    /** @var Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature[]  */
    protected $_featuresObjects = array();

    protected $_cache = array();

    //########################################

    public function __construct(array $arguments = array())
    {
        Mage::getSingleton('M2ePro/Upgrade_MySqlSetup_Autoloader')->register();

        list($this->_versionFrom, $this->_versionTo, $this->_installer) = $arguments;
        $this->_configObject = $this->buildConfigObject();

        $backupTables = array();

        foreach ($this->_configObject->getFeaturesList() as $featureName) {
            $featureObject = $this->buildFeatureObject($featureName);
            $backupTables = array_merge($backupTables, $featureObject->getBackupTables());

            $this->_featuresObjects[] = $featureObject;
        }

        $this->_backupObject = Mage::getModel(
            'M2ePro/Upgrade_Backup', array(
                $this->_installer, $backupTables
            )
        );
    }

    //########################################

    public function process()
    {
        foreach ($this->_featuresObjects as $featuresObject) {
            $featuresObject->execute();
        }
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Upgrade_Tables
     */
    public function getTablesObject()
    {
        $cacheKey = 'tablesObject';

        if (isset($this->_cache[$cacheKey])) {
            return $this->_cache[$cacheKey];
        }

        /** @var Ess_M2ePro_Model_Upgrade_Tables $object */
        $object = Mage::getModel('M2ePro/Upgrade_Tables', $this->_installer);

        return $this->_cache[$cacheKey] = $object;
    }

    // ---------------------------------------

    public function getCurrentSetupObject()
    {
        $setup = Mage::getModel('M2ePro/Setup')->getResource()->initCurrentSetupObject(
            $this->_versionFrom, $this->_versionTo
        );

        return $setup;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
     */
    protected function buildConfigObject()
    {
        if (empty($this->_versionFrom)) {
            return new Ess_M2ePro_Sql_Install_Config();
        }

        $className = sprintf(
            'Ess_M2ePro_Sql_Upgrade_v%s__v%s_Config',
            $this->prepareVersion($this->_versionFrom), $this->prepareVersion($this->_versionTo)
        );

        return new $className();
    }

    /**
     * @return Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
     */
    protected function buildFeatureObject($featureName)
    {
        if (empty($this->_versionFrom)) {
            $className = sprintf('Ess_M2ePro_Sql_Install_%s', $featureName);

            /** @var Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature $feature */
            $feature = new $className();
            $feature->setInstaller($this->_installer);

            return $feature;
        }

        if (strpos($featureName, '/') !== false && strpos($featureName, '@') === 0) {
            $featureName = explode('/', substr($featureName, 1));

            $className = sprintf(
                'Ess_M2ePro_Sql_Update_%s_%s',
                $featureName[0], $featureName[1]
            );
        } else {
            $className = sprintf(
                'Ess_M2ePro_Sql_Upgrade_v%s__v%s_%s',
                $this->prepareVersion($this->_versionFrom), $this->prepareVersion($this->_versionTo), $featureName
            );
        }

        /** @var Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature $feature */
        $feature = new $className();
        $feature->setInstaller($this->_installer);

        return $feature;
    }

    protected function prepareVersion($version)
    {
        return str_replace('.', '_', $version);
    }

    //########################################

    public function getBackupObject()
    {
        return $this->_backupObject;
    }

    public function getConfigObject()
    {
        return $this->_configObject;
    }

    //########################################
}
