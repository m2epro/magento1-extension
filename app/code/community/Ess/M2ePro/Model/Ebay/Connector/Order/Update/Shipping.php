<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_Order_Update_Shipping
    extends Ess_M2ePro_Model_Ebay_Connector_Order_Update_Abstract
{
    // M2ePro_TRANSLATIONS
    // Shipping Status for eBay Order was not updated. Reason: eBay Failure.
    // Tracking number "%num%" for "%code%" has been sent to eBay.
    // Shipping Status for eBay Order was updated to Shipped.

    private $carrierCode = NULL;
    private $trackingNumber = NULL;

    // ########################################

    public function setAction($action)
    {
        parent::setAction($action);

        if ($this->action == Ess_M2ePro_Model_Ebay_Connector_Order_Dispatcher::ACTION_SHIP_TRACK) {
            $this->carrierCode    = $this->params['carrier_code'];
            $this->trackingNumber = $this->params['tracking_number'];
        }
    }

    // ########################################

    protected function isNeedSendRequest()
    {
        if (!$this->order->getChildObject()->canUpdateShippingStatus($this->params)) {
            return false;
        }

        return parent::isNeedSendRequest();
    }

    // ########################################

    public function getRequestData()
    {
        $requestData = parent::getRequestData();

        if ($this->action == Ess_M2ePro_Model_Ebay_Connector_Order_Dispatcher::ACTION_SHIP_TRACK) {
            $requestData['carrier_code'] = $this->carrierCode;
            $requestData['tracking_number'] = $this->trackingNumber;
        }

        return $requestData;
    }

    // ########################################

    protected function prepareResponseData()
    {
        if ($this->getResponse()->isResultError()) {
            return;
        }

        $responseData = $this->getResponse()->getData();

        if (!isset($responseData['result']) || !$responseData['result']) {
            $this->order->addErrorLog(
                'Shipping Status for eBay Order was not updated. Reason: eBay Failure.'
            );

            return;
        }

        if ($this->action == Ess_M2ePro_Model_Ebay_Connector_Order_Dispatcher::ACTION_SHIP_TRACK) {
            $this->order->addSuccessLog(
                'Tracking number "%num%" for "%code%" has been sent to eBay.', array(
                    '!num'  => $this->trackingNumber,
                    '!code' => $this->carrierCode
                )
            );
        }

        if (!$this->order->getChildObject()->isShippingCompleted()) {
            $this->order->addSuccessLog(
                'Shipping Status for eBay Order was updated to Shipped.'
            );
        }

        if ($this->getOrderChangeId() !== null) {
            Mage::getResourceModel('M2ePro/Order_Change')->deleteByIds(array($this->getOrderChangeId()));
        }
    }

    // ########################################
}