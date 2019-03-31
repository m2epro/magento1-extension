<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_Order_Update_Payment
    extends Ess_M2ePro_Model_Ebay_Connector_Order_Update_Abstract
{
    // M2ePro_TRANSLATIONS
    // Payment Status for eBay Order was not updated. Reason: eBay Failure.
    // Payment Status for eBay Order was updated to Paid.

    // ########################################

    protected function prepareResponseData()
    {
        if ($this->getResponse()->isResultError()) {
            return;
        }

        $responseData = $this->getResponse()->getData();

        if (!isset($responseData['result']) || !$responseData['result']) {
            $this->order->addErrorLog(
                'Payment Status for eBay Order was not updated. Reason: eBay Failure.'
            );
            return;
        }

        $this->order->addSuccessLog('Payment Status for eBay Order was updated to Paid.');

        if (isset($responseData['is_already_paid']) && $responseData['is_already_paid']) {

            $this->order->setData('payment_status', Ess_M2ePro_Model_Ebay_Order::PAYMENT_STATUS_COMPLETED)->save();
            $this->order->updateMagentoOrderStatus();
        }

        if ($this->getOrderChangeId() !== null) {
            Mage::getResourceModel('M2ePro/Order_Change')->deleteByIds(array($this->getOrderChangeId()));
        }
    }

    // ########################################
}