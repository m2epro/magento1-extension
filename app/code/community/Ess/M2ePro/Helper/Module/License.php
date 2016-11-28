<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Module_License extends Mage_Core_Helper_Abstract
{
    const MODE_NONE = 0;
    const MODE_TRIAL = 1;
    const MODE_FREE = 2;
    const MODE_LIVE = 3;

    const STATUS_NONE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_SUSPENDED = 2;
    const STATUS_CLOSED = 3;
    const STATUS_CANCELED = 4;

    const IS_FREE_NO = 0;
    const IS_FREE_YES = 1;

    //########################################

    public function getKey()
    {
        $key = Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/','key'
        );
        return !is_null($key) ? (string)$key : '';
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

    public function getDirectory()
    {
        $directory = Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/','directory'
        );
        return !is_null($directory) ? (string)$directory : '';
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

    public function isValidDirectory()
    {
        $isValid = Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/valid/','directory');
        return is_null($isValid) || (bool)$isValid;
    }

    //########################################

    public function getMode($component)
    {
        $mode = (int)Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/','mode'
        );

        $validValues = array(self::MODE_NONE, self::MODE_TRIAL, self::MODE_FREE, self::MODE_LIVE);

        if (in_array($mode,$validValues)) {
            return $mode;
        }

        return self::MODE_NONE;
    }

    public function isNoneMode($component)
    {
        return $this->getMode($component) == self::MODE_NONE;
    }

    public function isTrialMode($component)
    {
        return $this->getMode($component) == self::MODE_TRIAL;
    }

    public function isFreeMode($component)
    {
        return $this->getMode($component) == self::MODE_FREE;
    }

    public function isLiveMode($component)
    {
        return $this->getMode($component) == self::MODE_LIVE;
    }

    // ---------------------------------------

    public function getStatus($component)
    {
        $status = (int)Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/','status'
        );

        $validValues = array(self::STATUS_NONE, self::STATUS_ACTIVE,
                             self::STATUS_SUSPENDED, self::STATUS_CLOSED, self::STATUS_CANCELED);

        if (in_array($status,$validValues)) {
            return $status;
        }

        return self::STATUS_NONE;
    }

    public function isNoneStatus($component)
    {
        return $this->getStatus($component) == self::STATUS_NONE;
    }

    public function isActiveStatus($component)
    {
        return $this->getStatus($component) == self::STATUS_ACTIVE;
    }

    public function isSuspendedStatus($component)
    {
        return $this->getStatus($component) == self::STATUS_SUSPENDED;
    }

    public function isClosedStatus($component)
    {
        return $this->getStatus($component) == self::STATUS_CLOSED;
    }

    public function isCanceledStatus($component)
    {
        return $this->getStatus($component) == self::STATUS_CANCELED;
    }

    // ---------------------------------------

    public function getIsFree($component)
    {
        $isFree = (int)Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/','is_free'
        );

        $validValues = array(self::IS_FREE_NO, self::IS_FREE_YES);

        if (in_array($isFree,$validValues)) {
            return $isFree;
        }

        return self::IS_FREE_YES;
    }

    public function isFreeEnabled($component)
    {
        return $this->getIsFree($component) == self::IS_FREE_YES;
    }

    public function isFreeDisabled($component)
    {
        return $this->getIsFree($component) == self::IS_FREE_NO;
    }

    //########################################

    public function isExpirationDate($component)
    {
        return $this->getIntervalBeforeExpirationDate($component) == 0;
    }

    public function getTimeStampExpirationDate($component)
    {
        $date = Mage::helper('M2ePro/Primary')->getConfig()->getGroupValue(
            '/'.Mage::helper('M2ePro/Module')->getName().'/license/'.strtolower($component).'/','expiration_date'
        );
        return (is_null($date) || $date == '') ?
                Mage::helper('M2ePro')->getCurrentGmtDate(true)-60*60*24 :
                (int)strtotime($date);
    }

    public function getTextExpirationDate($component,$withTime = false)
    {
        if ($withTime) {
            return Mage::helper('M2ePro')->gmtDateToTimezone(
                $this->getTimeStampExpirationDate($component)
            );
        } else {
            return Mage::helper('M2ePro')->gmtDateToTimezone(
                $this->getTimeStampExpirationDate($component),false,'Y-m-d'
            );
        }
    }

    public function getIntervalBeforeExpirationDate($component)
    {
        $timeStampCurrentDate = Mage::helper('M2ePro')->getCurrentGmtDate(true);
        $timeStampExpDate = $this->getTimeStampExpirationDate($component);

        if ($timeStampExpDate <= $timeStampCurrentDate) {
            return 0;
        }

        return $timeStampExpDate - $timeStampCurrentDate;
    }

    //########################################

    public function obtainRecord($email = NULL, $firstName = NULL, $lastName = NULL,
                                 $country = NULL, $city = NULL, $postalCode = NULL, $phone = NULL)
    {
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

            $dispatcherObject = Mage::getModel('M2ePro/Connector_M2ePro_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector('license', 'add', 'record',
                                                                   $requestParams);
            $response = $dispatcherObject->process($connectorObj);

        } catch (Exception $e) {
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
        if ($this->getKey() === '') {
            return false;
        }

        if (!$this->isNoneMode($component)) {
            return true;
        }

        try {

            $dispatcherObject = Mage::getModel('M2ePro/Connector_M2ePro_Dispatcher');
            $connectorObj = $dispatcherObject->getVirtualConnector('license','set','trial',
                                                                   array('key' => $this->getKey(),
                                                                         'component' => $component));
            $response = $dispatcherObject->process($connectorObj);

        } catch (Exception $exception) {
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