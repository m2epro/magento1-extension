<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Connector_Ebay_Order_Update_Payment
    extends Ess_M2ePro_Model_Connector_Ebay_Order_Update_Abstract
{
    // M2ePro_TRANSLATIONS
    // Payment Status for eBay Order was not updated. Reason: eBay Failure.
    // Payment Status for eBay Order was updated to Paid.

    // ########################################

    protected function prepareResponseData($response)
    {
        if ($this->resultType == parent::MESSAGE_TYPE_ERROR) {
            return $response;
        }

        if (!isset($response['result']) || !$response['result']) {
            $this->order->addErrorLog(
                'Payment Status for eBay Order was not updated. Reason: eBay Failure.'
            );
            return false;
        }

//        $this->order->setData('payment_status', Ess_M2ePro_Model_Ebay_Order::PAYMENT_STATUS_COMPLETED)->save();
        $this->order->addSuccessLog('Payment Status for eBay Order was updated to Paid.');

        Mage::getResourceModel('M2ePro/Order_Change')
            ->deleteByOrderAction($this->order->getId(), Ess_M2ePro_Model_Order_Change::ACTION_UPDATE_PAYMENT);

        return $response;
    }

    // ########################################
}