<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Connection_Request
{
    protected $_component        = null;
    protected $_componentVersion = null;
    protected $_command          = null;

    protected $_infoRewrites = array();
    protected $_data         = array();

    //########################################

    public function setComponent($value)
    {
        $this->_component = (string)$value;
        return $this;
    }

    public function getComponent()
    {
        return $this->_component;
    }

    // ----------------------------------------

    public function setComponentVersion($value)
    {
        $this->_componentVersion = (int)$value;
        return $this;
    }

    public function getComponentVersion()
    {
        return $this->_componentVersion;
    }

    // ----------------------------------------

    public function setCommand(array $value)
    {
        $value = array_values($value);

        if (count($value) != 3) {
            throw new Exception('Invalid Command Format.');
        }

        $this->_command = $value;
        return $this;
    }

    public function getCommand()
    {
        return $this->_command;
    }

    //########################################

    public function getInfo()
    {
        $data = array(
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
                'name' => $this->_component,
                'version' => $this->_componentVersion
            ),
            'command' => array(
                'entity' => $this->_command[0],
                'type' => $this->_command[1],
                'name' => $this->_command[2]
            )
        );

        $adminKey = Mage::helper('M2ePro/Server')->getAdminKey();
        $adminKey !== null && $adminKey != '' && $data['auth']['admin_key'] = $adminKey;

        $applicationKey = Mage::helper('M2ePro/Server')->getApplicationKey();
        $applicationKey !== null && $applicationKey != '' && $data['auth']['application_key'] = $applicationKey;

        $licenseKey = Mage::helper('M2ePro/Module_License')->getKey();
        $licenseKey !== null && $licenseKey != '' && $data['auth']['license_key'] = $licenseKey;

        $installationKey = Mage::helper('M2ePro/Module')->getInstallationKey();
        $installationKey !== null && $installationKey != '' && $data['auth']['installation_key'] = $installationKey;

        return array_merge_recursive($data, $this->_infoRewrites);
    }

    public function setInfoRewrites(array $value = array())
    {
        $this->_infoRewrites = $value;
        return $this;
    }

    // ---------------------------------------

    public function setData(array $value = array())
    {
        $this->_data = $value;
        return $this;
    }

    public function getData()
    {
        return $this->_data;
    }

    //########################################

    public function getPackage()
    {
        return array(
            'info' => $this->getInfo(),
            'data' => $this->getData()
        );
    }

    //########################################
}
