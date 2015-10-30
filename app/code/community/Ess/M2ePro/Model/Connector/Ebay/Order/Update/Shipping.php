<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Connector_Ebay_Order_Update_Shipping
    extends Ess_M2ePro_Model_Connector_Ebay_Order_Update_Abstract
{
    // M2ePro_TRANSLATIONS
    // Shipping Status for eBay Order was not updated. Reason: eBay Failure.
    // Tracking number "%num%" for "%code%" has been sent to eBay.
    // Shipping Status for eBay Order was updated to Shipped.

    private $carrierCode = NULL;
    private $trackingNumber = NULL;

    //########################################

    public function __construct(array $params = array(), Ess_M2ePro_Model_Order $order, $action)
    {
        parent::__construct($params, $order, $action);

        if ($this->action == Ess_M2ePro_Model_Connector_Ebay_Order_Dispatcher::ACTION_SHIP_TRACK) {
            $this->carrierCode = $params['carrier_code'];
            $this->trackingNumber = $params['tracking_number'];
        }
    }

    //########################################

    protected function isNeedSendRequest()
    {
        if (!$this->order->getChildObject()->canUpdateShippingStatus($this->params)) {
            return false;
        }

        return parent::isNeedSendRequest();
    }

    //########################################

    protected function getRequestData()
    {
        $requestData = parent::getRequestData();

        if ($this->action == Ess_M2ePro_Model_Connector_Ebay_Order_Dispatcher::ACTION_SHIP_TRACK) {
            $requestData['carrier_code'] = $this->carrierCode;
            $requestData['tracking_number'] = $this->trackingNumber;
        }

        return $requestData;
    }

    //########################################

    protected function prepareResponseData($response)
    {
        if ($this->resultType == parent::MESSAGE_TYPE_ERROR) {
            return $response;
        }

        if (!isset($response['result']) || !$response['result']) {
            $this->order->addErrorLog(
                'Shipping Status for eBay Order was not updated. Reason: eBay Failure.'
            );

            return false;
        }

        if ($this->action == Ess_M2ePro_Model_Connector_Ebay_Order_Dispatcher::ACTION_SHIP_TRACK) {
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

        Mage::getResourceModel('M2ePro/Order_Change')
            ->deleteByOrderAction($this->order->getId(), Ess_M2ePro_Model_Order_Change::ACTION_UPDATE_SHIPPING);

        return $response;
    }

    //########################################
}