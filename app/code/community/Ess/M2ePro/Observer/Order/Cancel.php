<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Observer_Order_Cancel extends Ess_M2ePro_Observer_Abstract
{
    //########################################

    public function process()
    {
        /** @var Mage_Sales_Model_Order $magentoOrder */
        $magentoOrder = $this->getEvent()->getOrder();
        $magentoOrderId = $magentoOrder->getId();

        try {
            /** @var $order Ess_M2ePro_Model_Order */
            $order = Mage::helper('M2ePro')->getObject('Order', $magentoOrderId, 'magento_order_id');
        } catch (Exception $e) {
            return;
        }

        if ($order === null) {
            return;
        }

        if ($order->getComponentMode() != Ess_M2ePro_Helper_Component_Ebay::NICK) {
            return;
        }

        $order->getLog()->setInitiator(Ess_M2ePro_Helper_Data::INITIATOR_EXTENSION);
        $order->getChildObject()->cancel();
    }

    //########################################
}
