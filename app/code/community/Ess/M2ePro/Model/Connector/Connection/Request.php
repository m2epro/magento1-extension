<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Connection_Request
{
    // ########################################

    private $component = NULL;
    private $componentVersion = NULL;
    private $command = NULL;

    private $infoRewrites = array();
    private $data = array();

    // ########################################

    public function setComponent($value)
    {
        $this->component = (string)$value;
        return $this;
    }

    public function getComponent()
    {
        return $this->component;
    }

    // ----------------------------------------

    public function setComponentVersion($value)
    {
        $this->componentVersion = (int)$value;
        return $this;
    }

    public function getComponentVersion()
    {
        return $this->componentVersion;
    }

    // ----------------------------------------

    public function setCommand(array $value)
    {
        $value = array_values($value);

        if (count($value) != 3) {
            throw new Exception('Invalid Command Format.');
        }

        $this->command = $value;
        return $this;
    }

    public function getCommand()
    {
        return $this->command;
    }

    // ########################################

    public function getInfo()
    {
        $data = array(
            'mode' => Mage::helper('M2ePro/Module')->isDevelopmentEnvironment() ? 'development' : 'production',
            'client' => array(
                'platform' => array(
                    'name' => Mage::helper('M2ePro/Magento')->getName().
                                ' ('.Mage::helper('M2ePro/Magento')->getEditionName().')',
                    'version' => Mage::helper('M2ePro/Magento')->getVersion(),
                    'revision' => Mage::helper('M2ePro/Magento')->getRevision(),
                ),
                'module' => array(
                    'name' => Mage::helper('M2ePro/Module')->getName(),
                    'version' => Mage::helper('M2ePro/Module')->getVersion(),
                    'revision' => Mage::helper('M2ePro/Module')->getRevision()
                ),
                'location' => array(
                    'domain' => Mage::helper('M2ePro/Client')->getDomain(),
                    'ip' => Mage::helper('M2ePro/Client')->getIp(),
                    'directory' => Mage::helper('M2ePro/Client')->getBaseDirectory()
                ),
                'locale' => Mage::helper('M2ePro/Magento')->getLocale()
            ),
            'auth' => array(),
            'component' => array(
                'name' => $this->component,
                'version' => $this->componentVersion
            ),
            'command' => array(
                'entity' => $this->command[0],
                'type' => $this->command[1],
                'name' => $this->command[2]
            )
        );

        $adminKey = Mage::helper('M2ePro/Server')->getAdminKey();
        !is_null($adminKey) && $adminKey != '' && $data['auth']['admin_key'] = $adminKey;

        $applicationKey = Mage::helper('M2ePro/Server')->getApplicationKey();
        !is_null($applicationKey) && $applicationKey != '' && $data['auth']['application_key'] = $applicationKey;

        $licenseKey = Mage::helper('M2ePro/Module_License')->getKey();
        !is_null($licenseKey) && $licenseKey != '' && $data['auth']['license_key'] = $licenseKey;

        $installationKey = Mage::helper('M2ePro/Module')->getInstallationKey();
        !is_null($installationKey) && $installationKey != '' && $data['auth']['installation_key'] = $installationKey;

        return array_merge_recursive($data,$this->infoRewrites);
    }

    public function setInfoRewrites(array $value = array())
    {
        $this->infoRewrites = $value;
        return $this;
    }

    // ---------------------------------------

    public function setData(array $value = array())
    {
        $this->data = $value;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    // ########################################

    public function getPackage()
    {
        return array(
            'info' => $this->getInfo(),
            'data' => $this->getData()
        );
    }

    // ########################################
}