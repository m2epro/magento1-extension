<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Order getParentObject()
 * @method Ess_M2ePro_Model_Resource_Amazon_Order getResource()
 */
class Ess_M2ePro_Model_Amazon_ThrottlingManager
{
    const REQUEST_TYPE_FEED   = 'feed';
    const REQUEST_TYPE_REPORT = 'report';

    const RESERVED_REQUESTS_REGISTRY_KEY = '/amazon/throttling/reserved_requests/';

    protected $_availableRequestsCount = array();

    //########################################

    public function getAvailableRequestsCount($merchantId, $requestType)
    {
        if (empty($this->_availableRequestsCount)) {
            $this->_availableRequestsCount = $this->receiveAvailableRequestsCount();
        }

        if (!isset($this->_availableRequestsCount[$merchantId][$requestType])) {
            return 0;
        }

        $availableRequestsCount = $this->_availableRequestsCount[$merchantId][$requestType];

        $requestsCount = $availableRequestsCount - $this->getReservedRequestsCount($merchantId, $requestType);

        return $requestsCount > 0 ? $requestsCount : 0;
    }

    public function registerRequests($merchantId, $requestType, $requestsCount)
    {
        if (!isset($this->_availableRequestsCount[$merchantId][$requestType])) {
            return;
        }

        if ($this->_availableRequestsCount[$merchantId][$requestType] <= 0) {
            return;
        }

        $this->_availableRequestsCount[$merchantId][$requestType] -= $requestsCount;

        if ($this->_availableRequestsCount[$merchantId][$requestType] <= 0) {
            $this->_availableRequestsCount[$merchantId][$requestType] = 0;
        }
    }

    //########################################

    public function getReservedRequestsCount($merchantId, $requestType)
    {
        $registry = Mage::getModel('M2ePro/Registry');
        $registry->load(self::RESERVED_REQUESTS_REGISTRY_KEY, 'key');

        $reservedRequests = $registry->getValueFromJson();

        if (!isset($reservedRequests[$merchantId][$requestType])) {
            return 0;
        }

        return (int)$reservedRequests[$merchantId][$requestType];
    }

    public function reserveRequests($merchantId, $requestType, $requestsCount)
    {
        $registry = Mage::getModel('M2ePro/Registry');
        $registry->load(self::RESERVED_REQUESTS_REGISTRY_KEY, 'key');

        $reservedRequests = $registry->getValueFromJson();

        if (!isset($reservedRequests[$merchantId][$requestType])) {
            $reservedRequests[$merchantId][$requestType] = 0;
        }

        $reservedRequests[$merchantId][$requestType] += $requestsCount;

        $registry->setData(
            array(
            'key'   => self::RESERVED_REQUESTS_REGISTRY_KEY,
            'value' => Mage::helper('M2ePro')->jsonEncode($reservedRequests),
            )
        );
        $registry->save();
    }

    public function releaseReservedRequests($merchantId, $requestType, $requestsCount)
    {
        $registry = Mage::getModel('M2ePro/Registry');
        $registry->load(self::RESERVED_REQUESTS_REGISTRY_KEY, 'key');

        $reservedRequests = $registry->getValueFromJson();

        if (!isset($reservedRequests[$merchantId][$requestType])) {
            return;
        }

        $reservedRequests[$merchantId][$requestType] -= $requestsCount;

        if ($reservedRequests[$merchantId][$requestType] <= 0) {
            unset($reservedRequests[$merchantId][$requestType]);
        }

        $registry->setData(
            array(
            'key'   => self::RESERVED_REQUESTS_REGISTRY_KEY,
            'value' => Mage::helper('M2ePro')->jsonEncode($reservedRequests),
            )
        );
        $registry->save();
    }

    //########################################

    protected function receiveAvailableRequestsCount()
    {
        /** @var Ess_M2ePro_Model_Resource_Account_Collection $accountCollection */
        $accountCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Account');

        /** @var Ess_M2ePro_Model_Account[] $accounts */
        $accounts = $accountCollection->getItems();
        if (empty($accounts)) {
            return array();
        }

        $dispatcher = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
        $connector = $dispatcher->getVirtualConnector(
            'account', 'get', 'throttlingInfo',
            array('accounts' => $accountCollection->getColumnValues('server_hash')),
            'data', NULL
        );

        $dispatcher->process($connector);

        $responseData = $connector->getResponseData();
        if (empty($responseData)) {
            return array();
        }

        $availableRequestsCount = array();

        foreach ($responseData as $serverHash => $accountData) {
            /** @var Ess_M2ePro_Model_Account $account */
            $account = $accountCollection->getItemByColumnValue('server_hash', $serverHash);

            /** @var Ess_M2ePro_Model_Amazon_Account $amazonAccount */
            $amazonAccount = $account->getChildObject();

            if (isset($availableRequestsCount[$amazonAccount->getMerchantId()])) {
                continue;
            }

            $availableRequestsCount[$amazonAccount->getMerchantId()] = $accountData;
        }

        return $availableRequestsCount;
    }

    //########################################
}
