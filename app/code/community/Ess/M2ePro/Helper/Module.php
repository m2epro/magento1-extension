<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Module extends Mage_Core_Helper_Abstract
{
    const SERVER_LOCK_NO  = 0;
    const SERVER_LOCK_YES = 1;

    const SERVER_MESSAGE_TYPE_NOTICE  = 0;
    const SERVER_MESSAGE_TYPE_ERROR   = 1;
    const SERVER_MESSAGE_TYPE_WARNING = 2;
    const SERVER_MESSAGE_TYPE_SUCCESS = 3;

    const ENVIRONMENT_PRODUCTION  = 'production';
    const ENVIRONMENT_DEVELOPMENT = 'development';
    const ENVIRONMENT_TESTING     = 'testing';

    const DEVELOPMENT_MODE_COOKIE_KEY = 'm2epro_development_mode';

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

    /**
     * @return Ess_M2ePro_Model_Config_Synchronization
     */
    public function getSynchronizationConfig()
    {
        return Mage::getSingleton('M2ePro/Config_Synchronization');
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
        $revision = '11020';

        if ($revision == str_replace('|','#','|REVISION|')) {
            $revision = (int)exec('svnversion');
            $revision == 0 && $revision = 'N/A';
            $revision .= '-dev';
        }

        return $revision;
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

    public function isLockedByServer()
    {
        $lock = (int)Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.$this->getName().'/server/', 'lock'
        );

        $validValues = array(self::SERVER_LOCK_NO, self::SERVER_LOCK_YES);

        if (in_array($lock,$validValues)) {
            return $lock;
        }

        return self::SERVER_LOCK_NO;
    }

    // ---------------------------------------

    public function getServerMessages()
    {
        $messages = Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.$this->getName().'/server/', 'messages'
        );

        $messages = (!is_null($messages) && $messages != '') ?
                    (array)json_decode((string)$messages,true) :
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

    public function isReadyToWork()
    {
        if (!Mage::helper('M2ePro/Module_Wizard')->isFinished('migrationToV6')) {
            return false;
        }

        if (!Mage::helper('M2ePro/View_Ebay')->isInstallationWizardFinished() &&
            !Mage::helper('M2ePro/View_Common')->isInstallationWizardFinished()) {

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

    public function getRequirementsInfo()
    {
        $clientPhpData = Mage::helper('M2ePro/Client')->getPhpSettings();

        $requirements = array (

            'php_version' => array(
                'title' => Mage::helper('M2ePro')->__('PHP Version'),
                'condition' => array(
                    'sign' => '>=',
                    'value' => '5.3.0'
                ),
                'current' => array(
                    'value' => Mage::helper('M2ePro/Client')->getPhpVersion(),
                    'status' => true
                )
            ),

            'memory_limit' => array(
                'title' => Mage::helper('M2ePro')->__('Memory Limit'),
                'condition' => array(
                    'sign' => '>=',
                    'value' => '256 MB'
                ),
                'current' => array(
                    'value' => (int)$clientPhpData['memory_limit'] . ' MB',
                    'status' => true
                )
            ),

            'magento_version' => array(
                'title' => Mage::helper('M2ePro')->__('Magento Version'),
                'condition' => array(
                    'sign' => '>=',
                    'value' => Mage::helper('M2ePro/Magento')->isEnterpriseEdition()   ? '1.7.0.0' :
                               (Mage::helper('M2ePro/Magento')->isProfessionalEdition() ? '1.7.0.0' : '1.4.1.0')
                ),
                'current' => array(
                    'value' => Mage::helper('M2ePro/Magento')->getVersion(false),
                    'status' => true
                )
            ),

            'max_execution_time' => array(
                'title' => Mage::helper('M2ePro')->__('Max Execution Time'),
                'condition' => array(
                    'sign' => '>=',
                    'value' => '360 sec'
                ),
                'current' => array(
                    'value' => is_null($clientPhpData['max_execution_time'])
                               ? 'unknown' : $clientPhpData['max_execution_time'] . ' sec',
                    'status' => true
                )
            )
        );

        foreach ($requirements as $key => &$requirement) {

            // max execution time is unlimited or fcgi handler
            if ($key == 'max_execution_time' &&
                ($clientPhpData['max_execution_time'] == 0 || is_null($clientPhpData['max_execution_time']))) {
                continue;
            }

            $requirement['current']['status'] = version_compare(
                $requirement['current']['value'],
                $requirement['condition']['value'],
                $requirement['condition']['sign']
            );
        }

        return $requirements;
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
        return (string)getenv('M2EPRO_ENV') == self::ENVIRONMENT_PRODUCTION ||
               (!$this->isDevelopmentEnvironment() && !$this->isTestingEnvironment());
    }

    public function isDevelopmentEnvironment()
    {
        return (string)getenv('M2EPRO_ENV') == self::ENVIRONMENT_DEVELOPMENT;
    }

    public function isTestingEnvironment()
    {
        return (string)getenv('M2EPRO_ENV') == self::ENVIRONMENT_TESTING;
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