<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_Order_Receive_Items
    extends Ess_M2ePro_Model_Ebay_Connector_Command_RealTime
{
    const TIMEOUT_ERRORS_COUNT_TO_RISE = 3;
    const TIMEOUT_RISE_ON_ERROR        = 30;
    const TIMEOUT_RISE_MAX_VALUE       = 1500;

    //########################################

    protected function getCommand()
    {
        return array('orders', 'get', 'items');
    }

    public function getRequestData()
    {
        $data = array(
            'from_update_date' => $this->_params['from_update_date'],
            'to_update_date' => $this->_params['to_update_date']
        );

        if (!empty($this->_params['job_token'])) {
            $data['job_token'] = $this->_params['job_token'];
        }

        return $data;
    }

    //########################################

    public function process()
    {
        $cacheConfig = Mage::helper('M2ePro/Module')->getCacheConfig();
        $cacheConfigGroup = '/ebay/synchronization/orders/receive/timeout';

        try {
            parent::process();
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
    }

    protected function buildConnectionInstance()
    {
        $connection = parent::buildConnectionInstance();
        $connection->setTimeout($this->getRequestTimeOut());

        return $connection;
    }

    //########################################

    protected function getRequestTimeOut()
    {
        $cacheConfig = Mage::helper('M2ePro/Module')->getCacheConfig();
        $cacheConfigGroup = '/ebay/synchronization/orders/receive/timeout';

        $rise = (int)$cacheConfig->getGroupValue($cacheConfigGroup, 'rise');
        $rise > self::TIMEOUT_RISE_MAX_VALUE && $rise = self::TIMEOUT_RISE_MAX_VALUE;

        return 300 + $rise;
    }

    //########################################
}
