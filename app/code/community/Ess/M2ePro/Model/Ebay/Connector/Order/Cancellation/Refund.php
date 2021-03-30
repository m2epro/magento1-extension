<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_Order_Cancellation_Refund
    extends Ess_M2ePro_Model_Ebay_Connector_Order_Cancellation_Abstract
{
    //########################################

    protected function getCommand()
    {
        return array('orders', 'refund', 'entity');
    }

    public function getRequestData()
    {
        return array(
            'cancelId'   => $this->_params['cancelId'],
            'refundDate' => $this->_params['refundDate'],
        );
    }

    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function processResponseData()
    {
        $this->_order->getLog()->setInitiator($this->_orderChange->getCreatorType());
        $this->_orderChange->deleteInstance();
        $this->_order->addSuccessLog('Order is refunded. Status is updated on eBay.');
    }

    //########################################
}
