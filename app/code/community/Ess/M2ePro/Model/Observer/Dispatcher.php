<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Observer_Dispatcher
{
    //########################################

    public function systemConfigurationSaveAction(Varien_Event_Observer $eventObserver)
    {
        $this->process('Magento_Configuration', $eventObserver, true);
    }

    //########################################

    public function catalogProductSaveBefore(Varien_Event_Observer $eventObserver)
    {
        $this->process('Product_AddUpdate_Before', $eventObserver);
    }

    public function catalogProductSaveAfter(Varien_Event_Observer $eventObserver)
    {
        $this->process('Product_AddUpdate_After', $eventObserver);
    }

    // ---------------------------------------

    public function catalogProductDeleteBefore(Varien_Event_Observer $eventObserver)
    {
        $this->process('Product_Delete', $eventObserver);
    }

    //########################################

    public function catalogProductAttributeUpdateBefore(Varien_Event_Observer $eventObserver)
    {
        $this->process('Product_Attribute_Update_Before', $eventObserver);
    }

    //########################################

    public function catalogCategoryChangeProducts(Varien_Event_Observer $eventObserver)
    {
        $this->process('Category', $eventObserver);
    }

    public function catalogInventoryStockItemSaveAfter(Varien_Event_Observer $eventObserver)
    {
        $this->process('StockItem', $eventObserver);
    }

    //########################################

    public function synchronizationBeforeStart(Varien_Event_Observer $eventObserver)
    {
        $this->process('Indexes_Disable', $eventObserver);
    }

    public function synchronizationAfterStart(Varien_Event_Observer $eventObserver)
    {
        $this->process('Indexes_Enable', $eventObserver);
    }

    //########################################

    public function salesOrderInvoicePay(Varien_Event_Observer $eventObserver)
    {
        $this->process('Invoice', $eventObserver);
    }

    public function salesOrderShipmentSaveAfter(Varien_Event_Observer $eventObserver)
    {
        $this->process('Shipment', $eventObserver);
    }

    public function salesOrderShipmentTrackSaveAfter(Varien_Event_Observer $eventObserver)
    {
        $this->process('Shipment_Track', $eventObserver);
    }

    //########################################

    public function orderView(Varien_Event_Observer $eventObserver)
    {
        // event dispatched for ALL rendered magento blocks, so we need to skip unnecessary blocks ASAP
        if (!($eventObserver->getEvent()->getBlock() instanceof Mage_Adminhtml_Block_Sales_Order_View)) {
            return;
        }

        $this->process('Order_View', $eventObserver);
    }

    public function shipmentViewBefore(Varien_Event_Observer $eventObserver)
    {
        // event dispatched for ALL rendered magento blocks, so we need to skip unnecessary blocks ASAP
        if (!($eventObserver->getEvent()->getBlock() instanceof Mage_Adminhtml_Block_Sales_Order_Shipment_Create) &&
            !($eventObserver->getEvent()->getBlock() instanceof Mage_Adminhtml_Block_Sales_Order_Shipment_View)
        ) {
            return;
        }

        $this->process('Shipment_View_Before', $eventObserver);
    }

    public function shipmentViewAfter(Varien_Event_Observer $eventObserver)
    {
        // event dispatched for ALL rendered magento blocks, so we need to skip unnecessary blocks ASAP
        if (!($eventObserver->getEvent()->getBlock() instanceof Mage_Adminhtml_Block_Sales_Order_Shipment_Create)) {
            return;
        }

        $this->process('Shipment_View_After', $eventObserver);
    }

    public function salesOrderCreditmemoRefund(Varien_Event_Observer $eventObserver)
    {
        $this->process('CreditMemo', $eventObserver);
    }

    public function salesOrderSaveAfter(Varien_Event_Observer $eventObserver)
    {
        $this->process('Order', $eventObserver);
    }

    public function salesConvertQuoteItemToOrderItem(Varien_Event_Observer $eventObserver)
    {
        $this->process('Order_Quote', $eventObserver);
    }

    //########################################

    public function associateEbayItemWithProduct(Varien_Event_Observer $eventObserver)
    {
        $this->process('Ebay_Order_Item', $eventObserver);
    }

    public function associateAmazonItemWithProduct(Varien_Event_Observer $eventObserver)
    {
        $this->process('Amazon_Order_Item', $eventObserver);
    }

    public function associateBuyItemWithProduct(Varien_Event_Observer $eventObserver)
    {
        $this->process('Buy_Order_Item', $eventObserver);
    }

    //########################################

    public function revertAmazonOrderedQty(Varien_Event_Observer $eventObserver)
    {
        $this->process('Amazon_Order', $eventObserver);
    }

    //########################################

    private function process($observerModel, Varien_Event_Observer $eventObserver, $forceRun = false)
    {
        if (!$forceRun &&
            (!Mage::helper('M2ePro/Module')->isReadyToWork() ||
             !Mage::helper('M2ePro/Component')->getActiveComponents())) {

            return;
        }

        try {

            /** @var Ess_M2ePro_Model_Observer_Abstract $observer */
            $observer = Mage::getModel('M2ePro/Observer_'.$observerModel);
            $observer->setEventObserver($eventObserver);

            if (!$observer->canProcess()) {
                return;
            }

            $observer->beforeProcess();
            $observer->process();
            $observer->afterProcess();

        } catch (Exception $exception) {
            Mage::helper('M2ePro/Module_Exception')->process($exception);
        }
    }

    //########################################
}