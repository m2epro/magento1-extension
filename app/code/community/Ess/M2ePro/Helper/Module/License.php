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
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/','key'
        );
        return !is_null($key) ? (string)$key : '';
    }

    // ---------------------------------------

    public function getStatus()
    {
        $status = Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/','status'
        );
        return (bool)$status;
    }

    // ---------------------------------------

    public function getDomain()
    {
        $domain = Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/','domain'
        );
        return !is_null($domain) ? (string)$domain : '';
    }

    public function getIp()
    {
        $ip = Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/','ip'
        );
        return !is_null($ip) ? (string)$ip : '';
    }

    // ---------------------------------------

    public function getEmail()
    {
        $email = Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/info/','email'
        );
        return !is_null($email) ? (string)$email : '';
    }

    // ---------------------------------------

    public function isValidDomain()
    {
        $isValid = Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/valid/','domain');
        return is_null($isValid) || (bool)$isValid;
    }

    public function isValidIp()
    {
        $isValid = Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/valid/','ip');
        return is_null($isValid) || (bool)$isValid;
    }

    //########################################

    public function obtainRecord($email = NULL, $firstName = NULL, $lastName = NULL,
                                 $country = NULL, $city = NULL, $postalCode = NULL, $phone = NULL)
    {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            return false;
        }

        $requestParams = array(
            'domain' => Mage::helper('M2ePro/Client')->getDomain(),
            'directory' => Mage::helper('M2ePro/Client')->getBaseDirectory()
        );

        !is_null($email) && $requestParams['email'] = $email;
        !is_null($firstName) && $requestParams['first_name'] = $firstName;
        !is_null($lastName) && $requestParams['last_name'] = $lastName;
        !is_null($phone) && $requestParams['phone'] = $phone;
        !is_null($country) && $requestParams['country'] = $country;
        !is_null($city) && $requestParams['city'] = $city;
        !is_null($postalCode) && $requestParams['postal_code'] = $postalCode;

        try {

            $dispatcherObject = Mage::getModel('M2ePro/M2ePro_Connector_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector('license', 'add', 'record',
                                                                   $requestParams);
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
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/','key',(string)$response['key']
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
            $connectorObj = $dispatcherObject->getVirtualConnector('license','set','trial',
                                                                   array('key' => $this->getKey(),
                                                                         'component' => $component));
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

    public function updateLicenseUserInfo($email = NULL, $firstName = NULL, $lastName = NULL,
                                          $country = NULL, $city = NULL, $postalCode = NULL, $phone = NULL)
    {
        if (Mage::helper('M2ePro/Server_Maintenance')->isNow()) {
            return false;
        }

        $requestParams['key'] = $this->getKey();

        !is_null($email) && $requestParams['email'] = $email;
        !is_null($firstName) && $requestParams['first_name'] = $firstName;
        !is_null($lastName) && $requestParams['last_name'] = $lastName;
        !is_null($phone) && $requestParams['phone'] = $phone;
        !is_null($country) && $requestParams['country'] = $country;
        !is_null($city) && $requestParams['city'] = $city;
        !is_null($postalCode) && $requestParams['postal_code'] = $postalCode;

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