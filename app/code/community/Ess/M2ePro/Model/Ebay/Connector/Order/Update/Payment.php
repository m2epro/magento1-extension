<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_Order_Update_Payment
    extends Ess_M2ePro_Model_Ebay_Connector_Order_Update_Abstract
{
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
                'Payment Status for eBay Order was not updated. Reason: eBay Failure.'
            );
            return;
        }

        $this->_order->addSuccessLog('Payment Status for eBay Order was updated to Paid.');

        if (isset($responseData['is_already_paid']) && $responseData['is_already_paid']) {
            $this->_order->setData('payment_status', Ess_M2ePro_Model_Ebay_Order::PAYMENT_STATUS_COMPLETED)->save();
            $this->_order->updateMagentoOrderStatus();
        }

        $orderChange->deleteInstance();
    }

    //########################################
}
