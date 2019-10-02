<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Observer_Invoice extends Ess_M2ePro_Model_Observer_Abstract
{
    //########################################

    public function process()
    {
        if (Mage::helper('M2ePro/Data_Global')->getValue('skip_invoice_observer')) {
            // Not process invoice observer when set such flag
            Mage::helper('M2ePro/Data_Global')->unsetValue('skip_invoice_observer');
            return;
        }

        /** @var $invoice Mage_Sales_Model_Order_Invoice */
        $invoice = $this->getEvent()->getInvoice();
        $magentoOrderId = $invoice->getOrderId();

        try {
            /** @var $order Ess_M2ePro_Model_Order */
            $order = Mage::helper('M2ePro/Component_Ebay')
                                ->getObject('Order', $magentoOrderId, 'magento_order_id');
        } catch (Exception $e) {
            return;
        }

        if (!$order->getChildObject()->canUpdatePaymentStatus()) {
            return;
        }

        $order->getLog()->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);
        $order->getChildObject()->updatePaymentStatus();
    }

    //########################################
}
