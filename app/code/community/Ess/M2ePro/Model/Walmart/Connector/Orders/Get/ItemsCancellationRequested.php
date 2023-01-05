<?php

/**
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Connector_Orders_Get_ItemsCancellationRequested
    extends Ess_M2ePro_Model_Walmart_Connector_Command_RealTime
{
    /**
     * @return array
     */
    public function getRequestData()
    {
        return array(
            'account' => $this->_params['account'],
            'from_create_date' => $this->_params['from_create_date'],
        );
    }

    /**
     * @return array
     */
    protected function getCommand()
    {
        return array('orders', 'get', 'itemsCancellationRequested');
    }

    /**
     * @return bool
     */
    protected function validateResponse()
    {
        $responseData = $this->getResponse()->getData();

        return isset($responseData['items']);
    }

    /**
     * @return void
     */
    protected function prepareResponseData()
    {
        $result = array();
        $responseData = $this->getResponse()->getData();

        foreach ($responseData['items'] as $item) {
            $result[] = array(
                'sku' => $item['sku'],
                'walmart_order_id' => $item['walmart_order_id'],
            );
        }

        $this->_responseData = $result;
    }
}
