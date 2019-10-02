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
        $key = Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/', 'key'
        );
        return $key !== null ? (string)$key : '';
    }

    // ---------------------------------------

    public function getStatus()
    {
        $status = Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/', 'status'
        );
        return (bool)$status;
    }

    // ---------------------------------------

    public function getDomain()
    {
        $domain = Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/', 'domain'
        );
        return $domain !== null ? (string)$domain : '';
    }

    public function getIp()
    {
        $ip = Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/', 'ip'
        );
        return $ip !== null ? (string)$ip : '';
    }

    // ---------------------------------------

    public function getEmail()
    {
        $email = Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/info/', 'email'
        );
        return $email !== null ? (string)$email : '';
    }

    // ---------------------------------------

    public function isValidDomain()
    {
        $isValid = Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/valid/', 'domain'
        );
        return $isValid === null || (bool)$isValid;
    }

    public function isValidIp()
    {
        $isValid = Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/valid/', 'ip'
        );
        return $isValid === null || (bool)$isValid;
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
            'domain' => Mage::helper('M2ePro/Client')->getDomain(),
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

        try {
            $dispatcherObject = Mage::getModel('M2ePro/M2ePro_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'license', 'add', 'record',
                $requestParams
            );
            $dispatcherObject->process($connectorObj);
            $response = $connectorObj->getResponseData();
        } catch (Exception $e) {
            Mage::helper('M2ePro/Module_Exception')->process($e);
            return false;
        }

        if (!isset($response['key'])) {
            return false;
        }

        Mage::helper('M2ePro/Primary')->getConfig()->setGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/', 'key', (string)$response['key']
        );

        Mage::getModel('M2ePro/Servicing_Dispatcher')->processTask(
            Mage::getModel('M2ePro/Servicing_Task_License')->getPublicNick()
        );

        return true;
    }

    public function setTrial($component)
    {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            return false;
        }

        if ($this->getKey() === '') {
            return false;
        }

        if (!$this->isNoneMode($component)) {
            return true;
        }

        try {
            $dispatcherObject = Mage::getModel('M2ePro/M2ePro_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector(
                'license', 'set', 'trial',
                array('key' => $this->getKey(),
                'component' => $component)
            );
            $dispatcherObject->process($connectorObj);
            $response = $connectorObj->getResponseData();
        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($e);
            return false;
        }

        if (!isset($response['status']) || !$response['status']) {
            return false;
        }

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

        try {
            /** @var Ess_M2ePro_Model_M2ePro_Connector_Dispatcher $dispatcherObject */
            $dispatcherObject = Mage::getModel('M2ePro/M2ePro_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector('license', 'update', 'record', $requestParams);
            $dispatcherObject->process($connectorObj);
        } catch (Exception $e) {
            Mage::helper('M2ePro/Module_Exception')->process($e);
            return false;
        }

        return true;
    }

    //########################################

    public function getUserInfo()
    {
        $defaultStoreId = Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId();

        $userId = Mage::getSingleton('admin/session')->getUser()->getId();
        $userInfo = Mage::getModel('admin/user')->load($userId)->getData();

        $tempPath = defined('Mage_Shipping_Model_Config::XML_PATH_ORIGIN_CITY')
            ? Mage_Shipping_Model_Config::XML_PATH_ORIGIN_CITY : 'shipping/origin/city';
        $userInfo['city'] = Mage::getStoreConfig($tempPath, $defaultStoreId);

        $tempPath = defined('Mage_Shipping_Model_Config::XML_PATH_ORIGIN_POSTCODE')
            ? Mage_Shipping_Model_Config::XML_PATH_ORIGIN_POSTCODE : 'shipping/origin/postcode';
        $userInfo['postal_code'] = Mage::getStoreConfig($tempPath, $defaultStoreId);

        $userInfo['country'] = Mage::getStoreConfig('general/country/default', $defaultStoreId);

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

    //########################################
}
