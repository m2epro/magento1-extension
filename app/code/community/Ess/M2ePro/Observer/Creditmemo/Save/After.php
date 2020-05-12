<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Observer_Creditmemo_Save_After extends Ess_M2ePro_Observer_Abstract
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

        if ($order->isComponentModeAmazon()) {
            $order->getChildObject()->sendCreditmemo();
        }
    }

    //########################################
}
