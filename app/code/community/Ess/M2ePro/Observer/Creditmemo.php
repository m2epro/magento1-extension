<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Observer_Creditmemo extends Ess_M2ePro_Observer_Abstract
{
    //########################################

    public function process()
    {
        /** @var Mage_Sales_Model_Order_Creditmemo $creditmemo */
        $creditmemo = $this->getEvent()->getCreditmemo();
        $magentoOrderId = $creditmemo->getOrderId();

        try {
            /** @var $order Ess_M2ePro_Model_Order */
            $order = Mage::helper('M2ePro')->getObject('Order', $magentoOrderId, 'magento_order_id');
        } catch (Exception $e) {
            return;
        }

        if ($order === null) {
            return;
        }

        $componentMode = ucfirst($order->getComponentMode());

        if (!Mage::helper("M2ePro/Component_{$componentMode}")->isEnabled()) {
            return;
        }

        $order->getLog()->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);

        /** @var Ess_M2ePro_Model_Order_Creditmemo_Handler $handler */
        $handler = Mage::getModel("M2ePro/{$componentMode}_Order_Creditmemo_Handler");
        $handler->handle($order, $creditmemo);
    }

    //########################################
}
