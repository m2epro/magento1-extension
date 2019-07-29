<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Upgrade_MySqlSetup_UpgradeManager
{
    /* @var Ess_M2ePro_Model_Upgrade_MySqlSetup */
    protected $installer;

    protected $versionFrom;
    protected $versionTo;

    // ---------------------------------------

    /* @var Ess_M2ePro_Model_Upgrade_Backup */
    protected $backupObject;

    /** @var Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig */
    protected $configObject;

    /** @var Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature[]  */
    protected $featuresObjects = array();

    // ---------------------------------------

    private $cache = array();

    //########################################

    public function __construct(array $arguments = array())
    {
        Mage::getSingleton('M2ePro/Upgrade_MySqlSetup_Autoloader')->register();

        list($this->versionFrom, $this->versionTo, $this->installer) = $arguments;
        $this->configObject = $this->buildConfigObject();

        $backupTables = array();

        foreach ($this->configObject->getFeaturesList() as $featureName) {

            $featureObject = $this->buildFeatureObject($featureName);
            $backupTables = array_merge($backupTables, $featureObject->getBackupTables());

            $this->featuresObjects[] = $featureObject;
        }

        $this->backupObject = Mage::getModel('M2ePro/Upgrade_Backup', array(
            $this->installer, $backupTables
        ));
    }

    //########################################

    public function process()
    {
        foreach ($this->featuresObjects as $featuresObject) {
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

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        /** @var Ess_M2ePro_Model_Upgrade_Tables $object */
        $object = Mage::getModel('M2ePro/Upgrade_Tables');
        $object->setInstaller($this->installer)->initialize();

        return $this->cache[$cacheKey] = $object;
    }

    // ---------------------------------------

    public function getCurrentSetupObject()
    {
        $setup = Mage::getModel('M2ePro/Setup')->getResource()->initCurrentSetupObject(
            $this->versionFrom, $this->versionTo
        );

        return $setup;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Upgrade_Feature_AbstractConfig
     */
    private function buildConfigObject()
    {
        if (empty($this->versionFrom)) {
            return new Ess_M2ePro_Sql_Install_Config();
        }

        $className = sprintf(
            'Ess_M2ePro_Sql_Upgrade_v%s__v%s_Config',
            $this->prepareVersion($this->versionFrom), $this->prepareVersion($this->versionTo)
        );

        return new $className();
    }

    /**
     * @return Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
     */
    private function buildFeatureObject($featureName)
    {
        if (empty($this->versionFrom)) {

            $className = sprintf('Ess_M2ePro_Sql_Install_%s', $featureName);

            /** @var Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature $feature */
            $feature = new $className();
            $feature->setInstaller($this->installer);

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
                $this->prepareVersion($this->versionFrom), $this->prepareVersion($this->versionTo), $featureName
            );
        }

        /** @var Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature $feature */
        $feature = new $className();
        $feature->setInstaller($this->installer);

        return $feature;
    }

    private function prepareVersion($version)
    {
        return str_replace('.', '_', $version);
    }

    //########################################

    public function getBackupObject()
    {
        return $this->backupObject;
    }

    public function getConfigObject()
    {
        return $this->configObject;
    }

    //########################################
}