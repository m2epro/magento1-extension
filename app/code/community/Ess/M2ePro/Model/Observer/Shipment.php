<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Observer_Shipment extends Ess_M2ePro_Model_Observer_Abstract
{
    //########################################

    public function process()
    {
        if (Mage::helper('M2ePro/Data_Global')->getValue('skip_shipment_observer')) {
            Mage::helper('M2ePro/Data_Global')->unsetValue('skip_shipment_observer');
            return;
        }

        /** @var $shipment Mage_Sales_Model_Order_Shipment */
        $shipment = $this->getEvent()->getShipment();
        $magentoOrderId = $shipment->getOrderId();

        try {
            /** @var $order Ess_M2ePro_Model_Order */
            $order = Mage::helper('M2ePro/Component')
                            ->getUnknownObject('Order', $magentoOrderId, 'magento_order_id');
        } catch (Exception $e) {
            return;
        }

        if (is_null($order)) {
            return;
        }

        if (!in_array($order->getComponentMode(), Mage::helper('M2ePro/Component')->getActiveComponents())) {
            return;
        }

        $order->getLog()->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);

        /** @var $shipmentHandler Ess_M2ePro_Model_Order_Shipment_Handler */
        $shipmentHandler = Mage::getModel('M2ePro/Order_Shipment_Handler')->factory($order->getComponentMode());
        $result = $shipmentHandler->handle($order, $shipment);

        switch ($result) {
            case Ess_M2ePro_Model_Order_Shipment_Handler::HANDLE_RESULT_SUCCEEDED:
                $this->addSessionSuccessMessage($order);
                break;
            case Ess_M2ePro_Model_Order_Shipment_Handler::HANDLE_RESULT_FAILED:
                $this->addSessionErrorMessage($order);
                break;
        }
    }

    //########################################

    private function addSessionSuccessMessage(Ess_M2ePro_Model_Order $order)
    {
        $message = '';

        switch ($order->getComponentMode()) {
            case Ess_M2ePro_Helper_Component_Ebay::NICK:
                $message = Mage::helper('M2ePro')->__('Shipping Status for eBay Order was updated.');
                break;
            case Ess_M2ePro_Helper_Component_Amazon::NICK:
                $message = Mage::helper('M2ePro')->__('Updating Amazon Order Status to Shipped in Progress...');
                break;
            case Ess_M2ePro_Helper_Component_Buy::NICK:
                $message = Mage::helper('M2ePro')->__('Updating Rakuten.com Order Status to Shipped in Progress...');
                break;
        }

        if ($message) {
            Mage::getSingleton('adminhtml/session')->addSuccess($message);
        }
    }

    private function addSessionErrorMessage(Ess_M2ePro_Model_Order $order)
    {
        if ($order->isComponentModeEbay()) {
            $url = Mage::helper('adminhtml')
                ->getUrl('M2ePro/adminhtml_ebay_log/order', array('order_id' => $order->getId()));
        } else {
            $url = Mage::helper('adminhtml')
                ->getUrl('M2ePro/adminhtml_common_log/order', array('order_id' => $order->getId()));
        }

        $chanelTitle = $order->getComponentTitle();
        // M2ePro_TRANSLATIONS
        // Shipping Status for %chanel_title% Order was not updated. View <a href="%url%" target="_blank" >Order Log</a> for more details.
        $message = Mage::helper('M2ePro')->__(
            'Shipping Status for %chanel_title% Order was not updated.'.
            ' View <a href="%url% target="_blank" >Order Log</a>'.
            ' for more details.',
            $chanelTitle, $url
        );

        Mage::getSingleton('adminhtml/session')->addError($message);
    }

    //########################################
}