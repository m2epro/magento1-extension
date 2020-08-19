<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Module_License extends Mage_Core_Helper_Abstract
{
    //########################################

    public function getKey()
    {
        return (string)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/license/', 'key');
    }

    public function getStatus()
    {
        return (bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/license/', 'status');
    }

    public function getDomain()
    {
        return (string)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/license/domain/', 'valid');
    }

    public function getIp()
    {
        return (string)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/license/ip/', 'valid');
    }

    public function getEmail()
    {
        return (string)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/license/info/', 'email');
    }

    public function isValidDomain()
    {
        $isValid = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/license/domain/', 'is_valid');
        return $isValid === null || (bool)$isValid;
    }

    public function isValidIp()
    {
        $isValid = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/license/ip/', 'is_valid');
        return $isValid === null || (bool)$isValid;
    }

    public function getRealDomain()
    {
        return (string)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/license/domain/', 'real');
    }

    public function getRealIp()
    {
        return (string)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue('/license/ip/', 'real');
    }

    //########################################

    public function obtainRecord(
        $email = null,
        $firstName = null,
        $lastName = null,
        $country = null,
        $city = null,
        $postalCode = null,
        $phone = null
    ) {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            return false;
        }

        $requestParams = array(
            'domain'    => Mage::helper('M2ePro/Client')->getDomain(),
            'directory' => Mage::helper('M2ePro/Client')->getBaseDirectory()
        );

        $data = array(
            'email'       => $email,
            'first_name'  => $firstName,
            'last_name'   => $lastName,
            'phone'       => $phone,
            'country'     => $country,
            'city'        => $city,
            'postal_code' => $postalCode
        );

        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }

            $requestParams[$key] = $value;
        }

        $dispatcherObject = Mage::getModel('M2ePro/M2ePro_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector(
            'license', 'add', 'record',
            $requestParams
        );
        $dispatcherObject->process($connectorObj);
        $response = $connectorObj->getResponseData();

        if (!isset($response['key'])) {
            return false;
        }

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/license/', 'key', (string)$response['key']);

        Mage::getModel('M2ePro/Servicing_Dispatcher')->processTask(
            Mage::getModel('M2ePro/Servicing_Task_License')->getPublicNick()
        );

        return true;
    }

    //########################################

    public function updateLicenseUserInfo(
        $email = null,
        $firstName = null,
        $lastName = null,
        $country = null,
        $city = null,
        $postalCode = null,
        $phone = null
    ) {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            return false;
        }

        $requestParams['key'] = $this->getKey();

        $data = array(
            'email'       => $email,
            'first_name'  => $firstName,
            'last_name'   => $lastName,
            'phone'       => $phone,
            'country'     => $country,
            'city'        => $city,
            'postal_code' => $postalCode
        );

        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }

            $requestParams[$key] = $value;
        }

        /** @var Ess_M2ePro_Model_M2ePro_Connector_Dispatcher $dispatcherObject */
        $dispatcherObject = Mage::getModel('M2ePro/M2ePro_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector('license', 'update', 'record', $requestParams);
        $dispatcherObject->process($connectorObj);

        return true;
    }

    //########################################

    public function getUserInfo()
    {
        $defaultStoreId = Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId();

        $userId = Mage::getSingleton('admin/session')->getUser()->getId();
        $userInfo = Mage::getModel('admin/user')->load($userId)->getData();

        $userInfo['city'] = Mage::getStoreConfig(Mage_Shipping_Model_Config::XML_PATH_ORIGIN_CITY, $defaultStoreId);
        $userInfo['postal_code'] = Mage::getStoreConfig(
            Mage_Shipping_Model_Config::XML_PATH_ORIGIN_POSTCODE,
            $defaultStoreId
        );

        $userInfo['country'] = Mage::helper('core')->getDefaultCountry($defaultStoreId);

        $requiredKeys = array(
            'email',
            'firstname',
            'lastname',
            'country',
            'city',
            'postal_code',
        );

        foreach ($userInfo as $key => $value) {
            if (!in_array($key, $requiredKeys)) {
                unset($userInfo[$key]);
            }
        }

        return $userInfo;
    }

    public function getData()
    {
        return array(
            'key'        => $this->getKey(),
            'status'     => $this->getStatus(),
            'domain'     => $this->getDomain(),
            'ip'         => $this->getIp(),
            'info'       => array(
                'email' => $this->getEmail()
            ),
            'valid'      => array(
                'domain' => $this->isValidDomain(),
                'ip'     => $this->isValidIp()
            ),
            'connection' => array(
                'domain'    => $this->getRealDomain(),
                'ip'        => $this->getRealIp()
            )
        );
    }

    //########################################
}
