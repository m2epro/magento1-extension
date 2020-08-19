<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Order getParentObject()
 * @method Ess_M2ePro_Model_Resource_Walmart_Order getResource()
 */
class Ess_M2ePro_Model_Walmart_ThrottlingManager
{
    const REQUEST_TYPE_UPDATE_DETAILS    = 'update_details';
    const REQUEST_TYPE_UPDATE_PRICE      = 'update_price';
    const REQUEST_TYPE_UPDATE_PROMOTIONS = 'update_promotions';
    const REQUEST_TYPE_UPDATE_QTY        = 'update_qty';
    const REQUEST_TYPE_UPDATE_LAG_TIME   = 'update_lag_time';

    const REGISTRY_KEY = '/walmart/listing/product/request/throttling/last_request_info/';

    //########################################

    public function getAvailableRequestsCount($accountId, $requestType)
    {
        $lastRequestInfo = Mage::helper('M2ePro/Module')->getRegistry()
            ->getValueFromJson(self::REGISTRY_KEY . $accountId . '/');

        $throttlingInfo = $this->getThrottlingInfo($requestType);

        if (empty($lastRequestInfo[$requestType])) {
            return $throttlingInfo['quota'];
        }

        $lastRequestInfo = $lastRequestInfo[$requestType];

        $currentDateTime = new DateTime(Mage::helper('M2ePro')->getCurrentGmtDate(), new DateTimeZone('UTC'));
        $lastRequestDateTime = new DateTime($lastRequestInfo['date'], new DateTimeZone('UTC'));

        $datesDiff = $currentDateTime->diff($lastRequestDateTime);

        if ($datesDiff->y > 0 || $datesDiff->m > 0 || $datesDiff->d > 0) {
            return $throttlingInfo['quota'];
        }

        $minutesFromLastRequest = $datesDiff->i + ($datesDiff->h * 60);

        $availableRequestsCount = (int)($minutesFromLastRequest * $throttlingInfo['restore_rate']) +
            $lastRequestInfo['available_requests_count'];

        if ($availableRequestsCount > $throttlingInfo['quota']) {
            return $throttlingInfo['quota'];
        }

        return $availableRequestsCount;
    }

    public function registerRequests($accountId, $requestType, $requestsCount)
    {
        $availableRequestsCount = $this->getAvailableRequestsCount($accountId, $requestType);
        if ($availableRequestsCount <= 0) {
            return;
        }

        $availableRequestsCount -= $requestsCount;
        if ($availableRequestsCount < 0) {
            $availableRequestsCount = 0;
        }

        $lastRequestInfo = array(
            'date' => Mage::helper('M2ePro')->getCurrentGmtDate(),
            'available_requests_count' => $availableRequestsCount,
        );

        $existedLastRequestInfo = Mage::helper('M2ePro/Module')->getRegistry()
            ->getValueFromJson(self::REGISTRY_KEY . $accountId . '/');
        $existedLastRequestInfo[$requestType] = $lastRequestInfo;

        Mage::helper('M2ePro/Module')->getRegistry()->setValue(
            self::REGISTRY_KEY . $accountId . '/',
            $existedLastRequestInfo
        );
    }

    //########################################

    protected function getThrottlingInfo($requestType)
    {
        $throttlingInfo = array(
            self::REQUEST_TYPE_UPDATE_DETAILS => array(
                'quota'        => 10,
                'restore_rate' => 0.16, // 10 per hour
            ),
            self::REQUEST_TYPE_UPDATE_PRICE => array(
                'quota'        => 10,
                'restore_rate' => 0.16, // 10 per hour
            ),
            self::REQUEST_TYPE_UPDATE_PROMOTIONS => array(
                'quota'        => 6,
                'restore_rate' => 0.0042, // 6 per day
            ),
            self::REQUEST_TYPE_UPDATE_QTY => array(
                'quota'        => 10,
                'restore_rate' => 0.16, // 10 per hour
            ),
            self::REQUEST_TYPE_UPDATE_LAG_TIME => array(
                'quota'        => 6,
                'restore_rate' => 0.0042, // 6 per day
            ),
        );

        return $throttlingInfo[$requestType];
    }

    //########################################
}
