<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Observer_CreditMemo extends Ess_M2ePro_Model_Observer_Abstract
{
    //########################################

    public function process()
    {
        /** @var Mage_Sales_Model_Order_Creditmemo $creditmemo */
        $creditmemo = $this->getEvent()->getCreditmemo();
        $magentoOrderId = $creditmemo->getOrderId();

        try {
            /** @var $order Ess_M2ePro_Model_Order */
            $order = Mage::helper('M2ePro/Component')->getUnknownObject(
                'Order', $magentoOrderId, 'magento_order_id'
            );

        } catch (Exception $e) {
            return;
        }

        if (is_null($order)) {
            return;
        }

        $components = array_intersect(
            Mage::helper('M2ePro/Component')->getActiveComponents(),
            array(Ess_M2ePro_Helper_Component_Amazon::NICK, Ess_M2ePro_Helper_Component_Walmart::NICK)
        );

        if (!in_array($order->getComponentMode(), $components)) {
            return;
        }

        $order->getLog()->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);

        /** @var Ess_M2ePro_Model_Order_CreditMemo_Handler $handler */
        $handler = Ess_M2ePro_Model_Order_CreditMemo_Handler::factory($order->getComponentMode());
        $handler->handle($order, $creditmemo);
    }

    //########################################
}