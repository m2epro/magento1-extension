<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Order_Creditmemo_Handler extends Ess_M2ePro_Model_Order_Creditmemo_Handler
{
    //########################################

    /**
     * @return string
     */
    public function getComponentMode()
    {
        return Ess_M2ePro_Helper_Component_Ebay::NICK;
    }

    //########################################

    protected function getItemsToRefund(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        return array();
    }

    /**
     * @param Ess_M2ePro_Model_Order $order
     * @param Mage_Sales_Model_Order_Creditmemo $creditmemo
     * @return int
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function handle(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        if ($order->getComponentMode() !== $this->getComponentMode()) {
            throw new InvalidArgumentException('Invalid component mode.');
        }

        if (!$order->getChildObject()->canRefund()) {
            return self::HANDLE_RESULT_SKIPPED;
        }

        return $order->getChildObject()->refund() ? self::HANDLE_RESULT_SUCCEEDED : self::HANDLE_RESULT_FAILED;
    }

    //########################################
}
