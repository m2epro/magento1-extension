<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Ebay_Order_Receive_Items
    extends Ess_M2ePro_Model_Connector_Ebay_Abstract
{
    const TIMEOUT_ERRORS_COUNT_TO_RISE = 3;
    const TIMEOUT_RISE_ON_ERROR        = 30;
    const TIMEOUT_RISE_MAX_VALUE       = 1500;

    // ########################################

    protected function getCommand()
    {
        return array('orders', 'get', 'items');
    }

    protected function getRequestData()
    {
        $data = array(
            'from_update_date' => $this->params['from_update_date'],
            'to_update_date' => $this->params['to_update_date']
        );

        if (!empty($this->params['job_token'])) {
            $data['job_token'] = $this->params['job_token'];
        }

        return $data;
    }

    // ########################################

    public function process()
    {
        $cacheConfig = Mage::helper('M2ePro/Module')->getCacheConfig();
        $cacheConfigGroup = '/ebay/synchronization/orders/receive/timeout';

        try {

            $result = parent::process();

        } catch (Ess_M2ePro_Model_Exception_Connection $exception) {

            $data = $exception->getAdditionalData();
            if (!empty($data['curl_error_number']) && $data['curl_error_number'] == CURLE_OPERATION_TIMEOUTED) {

                $fails = (int)$cacheConfig->getGroupValue($cacheConfigGroup, 'fails');
                $fails++;

                $rise = (int)$cacheConfig->getGroupValue($cacheConfigGroup, 'rise');
                $rise += self::TIMEOUT_RISE_ON_ERROR;

                if ($fails >= self::TIMEOUT_ERRORS_COUNT_TO_RISE && $rise <= self::TIMEOUT_RISE_MAX_VALUE) {

                    $fails = 0;
                    $cacheConfig->setGroupValue($cacheConfigGroup, 'rise', $rise);
                }
                $cacheConfig->setGroupValue($cacheConfigGroup, 'fails', $fails);
            }

            throw $exception;
        }

        $cacheConfig->setGroupValue($cacheConfigGroup, 'fails', 0);

        return $result;
    }

    // ########################################

    protected function getRequestTimeout()
    {
        $cacheConfig = Mage::helper('M2ePro/Module')->getCacheConfig();
        $cacheConfigGroup = '/ebay/synchronization/orders/receive/timeout';

        $rise = (int)$cacheConfig->getGroupValue($cacheConfigGroup, 'rise');
        $rise > self::TIMEOUT_RISE_MAX_VALUE && $rise = self::TIMEOUT_RISE_MAX_VALUE;

        return 300 + $rise;
    }

    // ########################################

    protected function validateResponseData($response)
    {
        return true;
    }

    protected function prepareResponseData($response)
    {
        return $response;
    }

    // ########################################
}