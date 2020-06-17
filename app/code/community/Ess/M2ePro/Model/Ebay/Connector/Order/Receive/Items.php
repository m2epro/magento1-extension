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
        $data = array();

        if (!empty($this->_params['from_update_date']) && !empty($this->_params['to_update_date'])) {
            $data['from_update_date'] = $this->_params['from_update_date'];
            $data['to_update_date'] = $this->_params['to_update_date'];
        }

        if (!empty($this->_params['from_create_date']) && !empty($this->_params['to_create_date'])) {
            $data['from_create_date'] = $this->_params['from_create_date'];
            $data['to_create_date'] = $this->_params['to_create_date'];
        }

        if (!empty($this->_params['job_token'])) {
            $data['job_token'] = $this->_params['job_token'];
        }

        return $data;
    }

    //########################################

    public function process()
    {
        try {
            parent::process();
        } catch (Ess_M2ePro_Model_Exception_Connection $exception) {
            $data = $exception->getAdditionalData();
            if (!empty($data['curl_error_number']) && $data['curl_error_number'] == CURLE_OPERATION_TIMEOUTED) {
                $fails = (int)Mage::helper('M2ePro/Module')->getRegistryValue(
                    '/ebay/synchronization/orders/receive/timeout_fails/'
                );
                $fails++;

                $rise = (int)Mage::helper('M2ePro/Module')->getRegistryValue(
                    '/ebay/synchronization/orders/receive/timeout_rise/'
                );
                $rise += self::TIMEOUT_RISE_ON_ERROR;

                if ($fails >= self::TIMEOUT_ERRORS_COUNT_TO_RISE && $rise <= self::TIMEOUT_RISE_MAX_VALUE) {
                    $fails = 0;
                    Mage::helper('M2ePro/Module')->setRegistryValue(
                        '/ebay/synchronization/orders/receive/timeout_rise/',
                        $rise
                    );
                }

                Mage::helper('M2ePro/Module')->setRegistryValue(
                    '/ebay/synchronization/orders/receive/timeout_fails/',
                    $fails
                );
            }

            throw $exception;
        }

        Mage::helper('M2ePro/Module')->setRegistryValue('/ebay/synchronization/orders/receive/timeout_fails/', 0);
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
        $rise = (int)Mage::helper('M2ePro/Module')->getRegistryValue(
            '/ebay/synchronization/orders/receive/timeout_rise/'
        );
        $rise > self::TIMEOUT_RISE_MAX_VALUE && $rise = self::TIMEOUT_RISE_MAX_VALUE;

        return 300 + $rise;
    }

    //########################################
}
