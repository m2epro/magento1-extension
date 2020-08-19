<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * Handles credit memos, created by seller in admin panel
 */
abstract class Ess_M2ePro_Model_Order_Creditmemo_Handler
{
    const HANDLE_RESULT_FAILED    = -1;
    const HANDLE_RESULT_SKIPPED   = 0;
    const HANDLE_RESULT_SUCCEEDED = 1;

    //########################################

    abstract protected function getComponentMode();
    abstract protected function getItemsToRefund(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Creditmemo $cm);

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

        $items = $this->getItemsToRefund($order, $creditmemo);
        return $order->getChildObject()->refund($items) ? self::HANDLE_RESULT_SUCCEEDED : self::HANDLE_RESULT_FAILED;
    }

    //########################################
}