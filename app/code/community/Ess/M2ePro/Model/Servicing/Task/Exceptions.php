<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Servicing_Task_Exceptions extends Ess_M2ePro_Model_Servicing_Task
{
    //########################################

    /**
     * @return string
     */
    public function getPublicNick()
    {
        return 'exceptions';
    }

    //########################################

    /**
     * @return array
     */
    public function getRequestData()
    {
        return array();
    }

    public function processResponseData(array $data)
    {
        $data = $this->prepareAndCheckReceivedData($data);

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/server/exceptions/', 'filters', (int)$data['is_filter_enable']
        );
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/server/fatal_error/', 'send', (int)$data['send_to_server']['fatal']
        );
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/server/exceptions/', 'send', (int)$data['send_to_server']['exception']
        );

        Mage::helper('M2ePro/Module')->getRegistry()->setValue('/exceptions_filters/', $data['filters']);
    }

    //########################################

    protected function prepareAndCheckReceivedData($data)
    {
        // Send To Server
        // ---------------------------------------
        if (!isset($data['send_to_server']['fatal']) || !is_bool($data['send_to_server']['fatal'])) {
            $data['send_to_server']['fatal'] = true;
        }

        if (!isset($data['send_to_server']['exception']) || !is_bool($data['send_to_server']['exception'])) {
            $data['send_to_server']['exception'] = true;
        }

        // ---------------------------------------

        // Exceptions Filters
        // ---------------------------------------
        if (!isset($data['is_filter_enable']) || !is_bool($data['is_filter_enable'])) {
            $data['is_filter_enable'] = false;
        }

        if (!isset($data['filters']) || !is_array($data['filters'])) {
            $data['filters'] = array();
        }

        $validatedFilters = array();

        $allowedFilterTypes = array(
            Ess_M2ePro_Helper_Module_Exception::FILTER_TYPE_TYPE,
            Ess_M2ePro_Helper_Module_Exception::FILTER_TYPE_INFO,
            Ess_M2ePro_Helper_Module_Exception::FILTER_TYPE_MESSAGE
        );

        foreach ($data['filters'] as $filter) {
            if (!isset($filter['preg_match']) || $filter['preg_match'] == '' ||
                !in_array($filter['type'], $allowedFilterTypes)) {
                continue;
            }

            $validatedFilters[] = $filter;
        }

        $data['filters'] = $validatedFilters;
        // ---------------------------------------

        return $data;
    }

    //########################################
}
