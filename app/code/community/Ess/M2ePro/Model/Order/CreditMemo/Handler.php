<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * Handles credit memos, created by seller in admin panel
 */
abstract class Ess_M2ePro_Model_Order_CreditMemo_Handler
{
    const HANDLE_RESULT_FAILED    = -1;
    const HANDLE_RESULT_SKIPPED   = 0;
    const HANDLE_RESULT_SUCCEEDED = 1;

    //########################################

    abstract public function handle(Ess_M2ePro_Model_Order $order, Mage_Sales_Model_Order_Creditmemo $creditmemo);

    //########################################

    public static function factory($component)
    {
        $handler = null;

        switch ($component) {
            case Ess_M2ePro_Helper_Component_Amazon::NICK:
                $handler = Mage::getModel('M2ePro/Amazon_Order_CreditMemo_Handler');
                break;
            case Ess_M2ePro_Helper_Component_Walmart::NICK:
                $handler = Mage::getModel('M2ePro/Walmart_Order_CreditMemo_Handler');
                break;
        }

        if (!$handler) {
            throw new Ess_M2ePro_Model_Exception_Logic('Credit Memo handler not found.');
        }

        return $handler;
    }

    //########################################
}