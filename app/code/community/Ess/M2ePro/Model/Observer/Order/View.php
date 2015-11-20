<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Observer_Order_View extends Ess_M2ePro_Model_Observer_Abstract
{
    //########################################

    public function process()
    {
        $block = $this->getEvent()->getBlock();

        if (!($block instanceof Mage_Adminhtml_Block_Sales_Order_View)) {
            return;
        }

        $magentoOrderId = $block->getRequest()->getParam('order_id');
        if (empty($magentoOrderId)) {
            return;
        }

        try {
            /** @var Ess_M2ePro_Model_Order $order */
            $order = Mage::helper('M2ePro/Component')->getUnknownObject(
                'Order', (int)$magentoOrderId, 'magento_order_id'
            );
        } catch (Exception $exception) {
            return;
        }

        if (is_null($order) || !$order->getId()) {
            return;
        }

        if (!Mage::helper('M2ePro/Component_'.ucfirst($order->getComponentMode()))->isActive()) {
            return;
        }

        if ($order->isComponentModeEbay()) {
            $buttonUrl = $block->getUrl('M2ePro/adminhtml_ebay_order/view', array('id' => $order->getId()));
        } else {
            $buttonUrl = $block->getUrl(
                'M2ePro/adminhtml_common_'.strtolower($order->getComponentMode()).'_order/view',
                array('id' => $order->getId())
            );
        }

        $componentTitles = Mage::helper('M2ePro/Component')->getComponentsTitles();
        $title = $componentTitles[$order->getComponentMode()];

        $block->addButton(
            'go_to_m2epro_order',
            array(
                'label' => Mage::helper('M2ePro')->__('Show %component% Order', $title),
                'onclick' => 'window.open(\''.$buttonUrl.'\')',
            ),
            0, -1
        );
    }

    //########################################
}