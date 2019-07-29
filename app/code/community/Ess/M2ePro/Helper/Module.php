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

    const ENVIRONMENT_PRODUCTION  = 'production';
    const ENVIRONMENT_DEVELOPMENT = 'development';
    const ENVIRONMENT_TESTING     = 'testing';

    const DEVELOPMENT_MODE_COOKIE_KEY = 'm2epro_development_mode';

    const IDENTIFIER = 'Ess_M2ePro';

    //########################################

    /**
     * @return Ess_M2ePro_Model_Config_Module
     */
    public function getConfig()
    {
        return Mage::getSingleton('M2ePro/Config_Module');
    }

    /**
     * @return Ess_M2ePro_Model_Config_Cache
     */
    public function getCacheConfig()
    {
        return Mage::getSingleton('M2ePro/Config_Cache');
    }

    //########################################

    public function getName()
    {
        return 'm2epro';
    }

    public function getVersion()
    {
        $version = (string)Mage::getConfig()->getNode('modules/Ess_M2ePro/version');
        $version = strtolower($version);

        if (Mage::helper('M2ePro/Data_Cache_Permanent')->getValue('MODULE_VERSION_UPDATER') === false) {
            Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
                '/modules/',$this->getName(),$version.'.r'.$this->getRevision()
            );
            Mage::helper('M2ePro/Data_Cache_Permanent')->setValue('MODULE_VERSION_UPDATER',array(),array(),60*60*24);
        }

        return $version;
    }

    public function getRevision()
    {
        return '14227';
    }

    // ---------------------------------------

    public function getVersionWithRevision()
    {
        return $this->getVersion().'r'.$this->getRevision();
    }

    //########################################

    public function getInstallationKey()
    {
        return Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.$this->getName().'/server/', 'installation_key'
        );
    }

    //########################################

    public function getServerMessages()
    {
        $messages = Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.$this->getName().'/server/', 'messages'
        );

        $messages = (!is_null($messages) && $messages != '') ?
                    (array)Mage::helper('M2ePro')->jsonDecode((string)$messages) :
                    array();

        $messages = array_filter($messages,array($this,'getServerMessagesFilterModuleMessages'));
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
        return (bool)$this->getConfig()->getGroupValue(NULL, 'is_disabled');
    }

    //########################################

    public function isReadyToWork()
    {
        if (!Mage::helper('M2ePro/View_Ebay')->isInstallationWizardFinished() &&
            !Mage::helper('M2ePro/View_Amazon')->isInstallationWizardFinished() &&
            !Mage::helper('M2ePro/View_Walmart')->isInstallationWizardFinished()) {

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

            if (preg_match('/\*.*$/',$item)) {
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

    private function getDirectories($dirPath)
    {
        $directoryIterator = new RecursiveDirectoryIterator($dirPath, FilesystemIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::SELF_FIRST);

        $directories = array();
        foreach ($iterator as $path) {
            $path->isDir() && $directories[] = rtrim($path->getPathname(),'/\\');
        }

        return $directories;
    }

    //########################################

    public function isDevelopmentMode()
    {
        return Mage::app()->getCookie()->get(self::DEVELOPMENT_MODE_COOKIE_KEY);
    }

    public function isProductionMode()
    {
        return !$this->isDevelopmentMode();
    }

    public function setDevelopmentModeMode($value)
    {
        $value ? Mage::app()->getCookie()->set(self::DEVELOPMENT_MODE_COOKIE_KEY, 'true', 60*60*24*31*12)
               : Mage::app()->getCookie()->delete(self::DEVELOPMENT_MODE_COOKIE_KEY);
    }

    // ---------------------------------------

    public function isProductionEnvironment()
    {
        return (string)$this->getConfig()->getGroupValue(NULL, 'environment') === self::ENVIRONMENT_PRODUCTION ||
               (!$this->isDevelopmentEnvironment() && !$this->isTestingEnvironment());
    }

    public function isDevelopmentEnvironment()
    {
        return (string)$this->getConfig()->getGroupValue(NULL, 'environment') === self::ENVIRONMENT_DEVELOPMENT;
    }

    public function isTestingEnvironment()
    {
        return (string)$this->getConfig()->getGroupValue(NULL, 'environment') === self::ENVIRONMENT_TESTING;
    }

    //########################################

    public function clearConfigCache()
    {
        $this->getCacheConfig()->clear();
    }

    public function clearCache()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeAllValues();
    }

    //########################################
}