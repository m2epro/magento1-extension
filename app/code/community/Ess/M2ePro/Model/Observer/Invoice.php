<?php

/*
 * @copyright  Copyright (c) 2015 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Invoice extends Ess_M2ePro_Model_Observer_Abstract
{
    //####################################

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

        $this->createChange($order);

        Mage::getSingleton('M2ePro/Order_Log_Manager')
            ->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);

        $result = $order->getChildObject()->updatePaymentStatus();

        $result ? $this->addSessionSuccessMessage()
                : $this->addSessionErrorMessage($order);
    }

    //####################################

    private function createChange(Ess_M2ePro_Model_Order $order)
    {
        $orderId   = $order->getId();
        $action    = Ess_M2ePro_Model_Order_Change::ACTION_UPDATE_PAYMENT;
        $creator   = Ess_M2ePro_Model_Order_Change::CREATOR_TYPE_OBSERVER;
        $component = $order->getComponentMode();

        Mage::getModel('M2ePro/Order_Change')->create($orderId, $action, $creator, $component, array());
    }

    //-------------------------------------

    private function addSessionSuccessMessage()
    {
        $message = Mage::helper('M2ePro')->__('Payment Status for eBay Order was updated to Paid.');
        Mage::getSingleton('adminhtml/session')->addSuccess($message);
    }

    private function addSessionErrorMessage(Ess_M2ePro_Model_Order $order)
    {
        $url = Mage::helper('adminhtml')
            ->getUrl('M2ePro/adminhtml_ebay_log/order', array('order_id' => $order->getId()));

        $channelTitle = $order->getComponentTitle();
        // M2ePro_TRANSLATIONS
        // Payment Status for %chanel_title% Order was not updated. View <a href="%url%" target="_blank">Order Log</a> for more details.
        $message  = Mage::helper('M2ePro')->__(
            'Payment Status for %chanel_title% Order was not updated.'.
            ' View <a href="%url%" target="_blank">Order Log</a> for more details.',
            $channelTitle, $url
        );

        Mage::getSingleton('adminhtml/session')->addError($message);
    }

    //####################################
}