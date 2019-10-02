<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Observer_Order extends Ess_M2ePro_Model_Observer_Abstract
{
    //########################################

    public function process()
    {
        /** @var Mage_Sales_Model_Order $magentoOrder */
        $magentoOrder = $this->getEvent()->getOrder();

        /** @var Ess_M2ePro_Model_Order $order */
        $order = $magentoOrder->getData(Ess_M2ePro_Model_Order::ADDITIONAL_DATA_KEY_IN_ORDER);

        if (empty($order)) {
            return;
        }

        if ($order->getData('magento_order_id') == $magentoOrder->getId()) {
            return;
        }

        $order->addData(
            array(
            'magento_order_id'                           => $magentoOrder->getId(),
            'magento_order_creation_failure'             => Ess_M2ePro_Model_Order::MAGENTO_ORDER_CREATION_FAILED_NO,
            'magento_order_creation_latest_attempt_date' => Mage::helper('M2ePro')->getCurrentGmtDate()
            )
        );

        $order->setMagentoOrder($magentoOrder);
        $order->save();
    }

    //########################################
}
