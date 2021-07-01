<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_Order_Update_Shipping
    extends Ess_M2ePro_Model_Ebay_Connector_Order_Update_Abstract
{
    protected $_carrierCode = null;
    protected $_trackingNumber = null;

    //########################################

    /**
     * @param $action
     * @return $this|Ess_M2ePro_Model_Ebay_Connector_Order_Update_Abstract
     */
    public function setAction($action)
    {
        parent::setAction($action);

        if ($this->_action == Ess_M2ePro_Model_Ebay_Connector_Order_Dispatcher::ACTION_SHIP_TRACK) {
            $this->_carrierCode = $this->_params['carrier_code'];
            $this->_trackingNumber = $this->_params['tracking_number'];
        }

        return $this;
    }

    //########################################

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function isNeedSendRequest()
    {
        if (!$this->_order->getChildObject()->canUpdateShippingStatus($this->_params)) {
            return false;
        }

        return parent::isNeedSendRequest();
    }

    //########################################

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getRequestData()
    {
        $requestData = parent::getRequestData();

        if ($this->_action == Ess_M2ePro_Model_Ebay_Connector_Order_Dispatcher::ACTION_SHIP_TRACK) {
            $requestData['carrier_code'] = $this->_carrierCode;
            $requestData['tracking_number'] = $this->_trackingNumber;
        }

        return $requestData;
    }

    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function prepareResponseData()
    {
        if ($this->getResponse()->isResultError()) {
            return;
        }

        /** @var Ess_M2ePro_Model_Order_Change $orderChange */
        $orderChange = Mage::getModel('M2ePro/Order_Change')->load($this->getOrderChangeId());
        $this->_order->getLog()->setInitiator($orderChange->getCreatorType());

        $responseData = $this->getResponse()->getData();

        if (!isset($responseData['result']) || !$responseData['result']) {
            $this->_order->addErrorLog(
                'Shipping Status for eBay Order was not updated. Reason: eBay Failure.'
            );

            return;
        }

        if ($this->_action == Ess_M2ePro_Model_Ebay_Connector_Order_Dispatcher::ACTION_SHIP_TRACK) {
            $this->_order->addSuccessLog(
                'Tracking number "%num%" for "%code%" has been sent to eBay.',
                array(
                    '!num'  => $this->_trackingNumber,
                    '!code' => $this->_carrierCode
                )
            );
        }

        if (!$this->_order->getChildObject()->isShippingCompleted()) {
            $this->_order->addSuccessLog(
                'Shipping status [Shipped] was sent to eBay.'
            );
        }

        $orderChange->deleteInstance();
    }

    //########################################
}
