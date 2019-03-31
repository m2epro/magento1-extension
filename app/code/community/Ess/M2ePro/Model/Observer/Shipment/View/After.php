<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Observer_Shipment_View_After extends Ess_M2ePro_Model_Observer_Abstract
{
    //########################################

    public function process()
    {
        /** @var Mage_Adminhtml_Block_Sales_Order_Shipment_Create $block */
        $block = $this->getEvent()->getBlock();

        if (!($block instanceof Mage_Adminhtml_Block_Sales_Order_Shipment_Create)) {
            return;
        }

        $orderId = $block->getRequest()->getParam('order_id');
        if (empty($orderId)) {
            return;
        }

        try {
            /** @var Ess_M2ePro_Model_Order $order */
            $order = Mage::helper('M2ePro/Component_Amazon')->getObject('Order', (int)$orderId, 'magento_order_id');
        } catch (Exception $exception) {
            return;
        }

        if (is_null($order) || !$order->getId()) {
            return;
        }

        /** @var Ess_M2ePro_Model_Amazon_Order $amazonOrder */
        $amazonOrder = $order->getChildObject();

        if (!$amazonOrder->isEligibleForMerchantFulfillment() || $amazonOrder->isMerchantFulfillmentApplied()) {
            return;
        }

        $generalBlock = $block->getLayout()->createBlock('M2ePro/adminhtml_general');

        /** @var Ess_M2ePro_Block_Adminhtml_Amazon_Order_MerchantFulfillment_Magento_Shipment $amazonBlock */
        $amazonBlock = $block->getLayout()->createBlock(
            'M2ePro/adminhtml_amazon_order_merchantFulfillment_magento_shipment'
        );
        $amazonBlock->setOrder($order);

        /** @var Varien_Object $transport */
        $transport = $this->getEvent()->getTransport();
        $transport->setData('html', $transport->getData('html').$generalBlock->toHtml().$amazonBlock->toHtml());
    }

    //########################################
}