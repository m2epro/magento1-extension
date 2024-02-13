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

    /**
     * @return Ess_M2ePro_Model_Registry_Manager
     */
    public function getRegistry()
    {
        return Mage::getSingleton('M2ePro/Registry_Manager');
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Registration_Manager
     */
    public function getRegistration()
    {
        return Mage::getSingleton('M2ePro/Registration_Manager');
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

    public function getServerMessages()
    {
        $messages = $this->getRegistry()->getValueFromJson('/server/messages/');

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
