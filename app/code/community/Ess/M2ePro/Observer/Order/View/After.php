<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Observer_Order_View_After extends Ess_M2ePro_Observer_Abstract
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

        if ($order === null || !$order->getId()) {
            return;
        }

        if (!Mage::helper('M2ePro/Component_'.ucfirst($order->getComponentMode()))->isEnabled()) {
            return;
        }

        if ($order->isComponentModeEbay()) {
            $buttonUrl = $block->getUrl('M2ePro/adminhtml_ebay_order/view', array('id' => $order->getId()));

            Mage::helper('adminhtml')->setPageHelpUrl(
                Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(
                    null, null, "sales-and-orders#b53bfe179d0c4e8487dca22009ddaadf"
                )
            );
        } else {
            $buttonUrl = $block->getUrl(
                'M2ePro/adminhtml_'.strtolower($order->getComponentMode()).'_order/view',
                array('id' => $order->getId())
            );

            Mage::helper('adminhtml')->setPageHelpUrl(
                Mage::helper('M2ePro/Module_Support')->getDocumentationUrl(
                    null, null, "sales-and-orders"
                )
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