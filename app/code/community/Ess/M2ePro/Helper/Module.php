<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Module extends Mage_Core_Helper_Abstract
{
    const SERVER_MESSAGE_TYPE_NOTICE  = 0;
    const SERVER_MESSAGE_TYPE_ERROR   = 1;
    const SERVER_MESSAGE_TYPE_WARNING = 2;
    const SERVER_MESSAGE_TYPE_SUCCESS = 3;

    const ENVIRONMENT_PRODUCTION     = 'production';
    const ENVIRONMENT_DEVELOPMENT    = 'development';
    const ENVIRONMENT_TESTING_MANUAL = 'testing-manual';
    const ENVIRONMENT_TESTING_AUTO   = 'testing-auto';

    const IDENTIFIER = 'Ess_M2ePro';

    //########################################

    /**
     * @return Ess_M2ePro_Model_Config_Manager
     */
    public function getConfig()
    {
        return Mage::getSingleton('M2ePro/Config_Manager');
    }

    //########################################

    public function setRegistryValue($key, $value)
    {
        $registryModel = Mage::getModel('M2ePro/Registry')->loadByKey($key);
        $registryModel->setValue($value);
        $registryModel->save();
    }

    public function getRegistryValue($key)
    {
        return Mage::getModel('M2ePro/Registry')->loadByKey($key)->getValue();
    }

    public function deleteRegistryValue($key)
    {
        $registryModel = Mage::getModel('M2ePro/Registry');
        $registryModel->load($key, 'key');

        if ($registryModel->getId()) {
            $registryModel->delete();
        }
    }

    //########################################

    public function getName()
    {
        return 'm2epro';
    }

    // ---------------------------------------

    /**
     * Backward compatibility with M2eProUpdater
     * @deprecated use getPublicVersion()
     */
    public function getVersion()
    {
        return $this->getPublicVersion();
    }

    public function getPublicVersion()
    {
        $composerFile = Mage::getConfig()->getModuleDir(null, self::IDENTIFIER) .DS. 'composer.json';
        $composerData = Mage::helper('M2ePro/Data')->jsonDecode(file_get_contents($composerFile));

        return isset($composerData['version']) ? $composerData['version'] : '1.0.0';
    }

    public function getSetupVersion()
    {
        $version = (string)Mage::getConfig()->getNode('modules/Ess_M2ePro/version');
        return strtolower($version);
    }

    //########################################

    public function getInstallationKey()
    {
        return Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/', 'installation_key');
    }

    //########################################

    public function getServerMessages()
    {
        /** @var Ess_M2ePro_Model_Registry $registry */
        $registry = Mage::getModel('M2ePro/Registry')->loadByKey('/server/messages/');
        $messages = $registry->getValueFromJson();

        $messages = array_filter($messages, array($this,'getServerMessagesFilterModuleMessages'));
        !is_array($messages) && $messages = array();

        return $messages;
    }

    public function getServerMessagesFilterModuleMessages($message)
    {
        if (!isset($message['text']) || !isset($message['type'])) {
            return false;
        }

        return true;
    }

    //########################################

    public function isDisabled()
    {
        return (bool)$this->getConfig()->getGroupValue('/', 'is_disabled');
    }

    //########################################

    public function isReadyToWork()
    {
        if (!Mage::helper('M2ePro/View_Ebay')->isInstallationWizardFinished() &&
            !Mage::helper('M2ePro/View_Amazon')->isInstallationWizardFinished() &&
            !Mage::helper('M2ePro/View_Walmart')->isInstallationWizardFinished()
        ) {
            return false;
        }

        return true;
    }

    //########################################

    public function getFoldersAndFiles()
    {
        $paths = array(
            'app/code/community/Ess/',
            'app/code/community/Ess/M2ePro/*',

            'app/locale/*/Ess_M2ePro.csv',
            'app/etc/modules/Ess_M2ePro.xml',
            'app/design/adminhtml/default/default/layout/M2ePro.xml',

            'js/M2ePro/*',
            'skin/adminhtml/default/default/M2ePro/*',
            'skin/adminhtml/default/enterprise/M2ePro/*',
            'app/design/adminhtml/default/default/template/M2ePro/*'
        );

        return $paths;
    }

    //########################################

    public function getUnWritableDirectories()
    {
        $directoriesForCheck = array();
        foreach ($this->getFoldersAndFiles() as $item) {
            $fullDirPath = Mage::getBaseDir().DS.$item;

            if (preg_match('/\*.*$/', $item)) {
                $fullDirPath = preg_replace('/\*.*$/', '', $fullDirPath);
                $directoriesForCheck = array_merge($directoriesForCheck, $this->getDirectories($fullDirPath));
            }

            $directoriesForCheck[] = dirname($fullDirPath);
            is_dir($fullDirPath) && $directoriesForCheck[] = rtrim($fullDirPath, '/\\');
        }

        $directoriesForCheck = array_unique($directoriesForCheck);

        $unWritableDirs = array();
        foreach ($directoriesForCheck as $directory) {
            !is_dir_writeable($directory) && $unWritableDirs[] = $directory;
        }

        return $unWritableDirs;
    }

    protected function getDirectories($dirPath)
    {
        $directoryIterator = new RecursiveDirectoryIterator($dirPath, FilesystemIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::SELF_FIRST);

        $directories = array();
        foreach ($iterator as $path) {
            $path->isDir() && $directories[] = rtrim($path->getPathname(), '/\\');
        }

        return $directories;
    }

    //########################################

    public function getEnvironment()
    {
        return $this->getConfig()->getGroupValue('/', 'environment');
    }

    public function isProductionEnvironment()
    {
        return $this->getEnvironment() === null || $this->getEnvironment() === self::ENVIRONMENT_PRODUCTION;
    }

    public function isDevelopmentEnvironment()
    {
        return $this->getEnvironment() === self::ENVIRONMENT_DEVELOPMENT;
    }

    public function isTestingManualEnvironment()
    {
        return $this->getEnvironment() === self::ENVIRONMENT_TESTING_MANUAL;
    }

    public function isTestingAutoEnvironment()
    {
        return $this->getEnvironment() === self::ENVIRONMENT_TESTING_AUTO;
    }

    public function setEnvironment($env)
    {
        $this->getConfig()->setGroupValue('/', 'environment', $env);
    }

    //########################################

    public function clearCache()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeAllValues();
    }

    //########################################
}
