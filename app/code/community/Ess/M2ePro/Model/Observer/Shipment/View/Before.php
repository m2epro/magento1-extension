<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Observer_Shipment_View_Before extends Ess_M2ePro_Model_Observer_Abstract
{
    //########################################

    public function process()
    {
        $block = $this->getEvent()->getBlock();

        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Shipment_Create) {
            $this->processNewShipment($block);
        }

        if ($block instanceof Mage_Adminhtml_Block_Sales_Order_Shipment_View) {
            $this->processExistShipment($block);
        }
    }

    //########################################

    private function processNewShipment(Mage_Adminhtml_Block_Sales_Order_Shipment_Create $block)
    {
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

        $themeFileName = 'prototype/windows/themes/magento.css';
        $themeLibFileName = 'lib/'.$themeFileName;
        $themeFileFound = false;
        $skinBaseDir = Mage::getDesign()->getSkinBaseDir(
            array(
                '_package' => Mage_Core_Model_Design_Package::DEFAULT_PACKAGE,
                '_theme' => Mage_Core_Model_Design_Package::DEFAULT_THEME,
            )
        );

        if (!$themeFileFound && is_file($skinBaseDir .'/'.$themeLibFileName)) {
            $themeFileFound = true;
            $block->getLayout()->getBlock('head')->addCss($themeLibFileName);
        }

        if (!$themeFileFound && is_file(Mage::getBaseDir().'/js/'.$themeFileName)) {
            $themeFileFound = true;
            $block->getLayout()->getBlock('head')->addItem('js_css', $themeFileName);
        }

        if (!$themeFileFound) {
            $block->getLayout()->getBlock('head')->addCss($themeLibFileName);
            $block->getLayout()->getBlock('head')->addItem('js_css', $themeFileName);
        }

        $block->getLayout()->getBlock('head')
            ->addJs('prototype/window.js')
            ->addJs('M2ePro/General/CommonHandler.js')
            ->addJs('M2ePro/General/PhpHandler.js')
            ->addJs('M2ePro/General/TranslatorHandler.js')
            ->addJs('M2ePro/General/UrlHandler.js')
            ->addJs('M2ePro/Amazon/Order/MerchantFulfillment/MagentoHandler.js')
            ->addItem('js_css', 'prototype/windows/themes/default.css');
    }

    private function processExistShipment(Mage_Adminhtml_Block_Sales_Order_Shipment_View $block)
    {
        $shipmentId = $block->getRequest()->getParam('shipment_id');
        if (empty($shipmentId)) {
            return;
        }

        /** @var Mage_Sales_Model_Order_Shipment $shipment */
        $shipment = Mage::getModel('sales/order_shipment')->load((int)$shipmentId);

        try {
            /** @var Ess_M2ePro_Model_Order $order */
            $order = Mage::helper('M2ePro/Component_Amazon')->getObject(
                'Order', (int)$shipment->getOrderId(), 'magento_order_id'
            );
        } catch (Exception $exception) {
            return;
        }

        if (is_null($order) || !$order->getId()) {
            return;
        }

        if (!$order->isMagentoShipmentCreatedByOrder($shipment)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Amazon_Order $amazonOrder */
        $amazonOrder = $order->getChildObject();

        if (!$amazonOrder->isMerchantFulfillmentApplied() || !$amazonOrder->getData('merchant_fulfillment_label')) {
            return;
        }

        $getLabelUrl = $block->getUrl(
            'M2ePro/adminhtml_amazon_order_merchantFulfillment/getLabel',
            array('order_id' => $order->getId())
        );

        $block->updateButton('print', 'onclick', 'window.open(\''.$getLabelUrl.'\')');
    }

    //########################################
}