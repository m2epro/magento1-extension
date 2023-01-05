<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Observer_Dispatcher
{
    //########################################

    public function systemConfigurationInit(Varien_Event_Observer $eventObserver)
    {
        $this->process('Magento_Configuration_Init', $eventObserver, true);
    }

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

    public function catalogProductWebsiteUpdateBefore(Varien_Event_Observer $eventObserver)
    {
        $this->process('Product_Website_Update_Before', $eventObserver);
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

    public function salesOrderInvoiceSaveAfter(Varien_Event_Observer $eventObserver)
    {
        $this->process('Invoice_Save_After', $eventObserver);
    }

    public function salesShipmentItemSaveAfter(Varien_Event_Observer $eventObserver)
    {
        $this->process('Shipment_Item', $eventObserver);
    }

    public function salesOrderShipmentTrackSaveAfter(Varien_Event_Observer $eventObserver)
    {
        $this->process('Shipment_Track', $eventObserver);
    }

    //########################################

    public function orderViewAfter(Varien_Event_Observer $eventObserver)
    {
        // event dispatched for ALL rendered magento blocks, so we need to skip unnecessary blocks ASAP
        if (!($eventObserver->getEvent()->getBlock() instanceof Mage_Adminhtml_Block_Sales_Order_View)) {
            return;
        }

        $this->process('Order_View_After', $eventObserver);
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

    public function orderNotification(Varien_Event_Observer $eventObserver)
    {
        $this->process('Order_Notification', $eventObserver);
    }

    public function orderQuoteTotal(Varien_Event_Observer $eventObserver)
    {
        $this->process('Order_Quote_Address_Collect_Totals_After', $eventObserver);
    }

    public function salesOrderCreditmemoRefund(Varien_Event_Observer $eventObserver)
    {
        $this->process('Creditmemo', $eventObserver);
    }

    public function salesOrderCreditmemoSaveAfter(Varien_Event_Observer $eventObserver)
    {
        $this->process('Creditmemo_Save_After', $eventObserver);
    }

    public function salesOrderSaveAfterStoreMagentoOrderId(Varien_Event_Observer $eventObserver)
    {
        $this->process('Order_Save_After_StoreMagentoOrderId', $eventObserver);
    }

    public function salesConvertQuoteItemToOrderItem(Varien_Event_Observer $eventObserver)
    {
        $this->process('Order_Quote', $eventObserver);
    }

    public function orderViewBefore(Varien_Event_Observer $eventObserver)
    {
        $this->process('Order_View_Before', $eventObserver);
    }

    public function salesOrderCancel(Varien_Event_Observer $eventObserver)
    {
        $this->process('Order_Cancel', $eventObserver);
    }

    public function shipmentView(Varien_Event_Observer $eventObserver)
    {
        $this->process('Shipment_View', $eventObserver);
    }

    public function invoiceView(Varien_Event_Observer $eventObserver)
    {
        $this->process('Invoice_View', $eventObserver);
    }

    public function creditmemoView(Varien_Event_Observer $eventObserver)
    {
        $this->process('Creditmemo_View', $eventObserver);
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

    public function associateWalmartItemWithProduct(Varien_Event_Observer $eventObserver)
    {
        $this->process('Walmart_Order_Item', $eventObserver);
    }

    //########################################

    public function revertAmazonOrderedQty(Varien_Event_Observer $eventObserver)
    {
        $this->process('Amazon_Order', $eventObserver);
    }

    //########################################

    public function listingProductSaveAfter(Varien_Event_Observer $eventObserver)
    {
        $this->process('Listing_Product_Save_After', $eventObserver);
    }

    public function listingProductDeleteBefore(Varien_Event_Observer $eventObserver)
    {
        $this->process('Listing_Product_Delete_Before', $eventObserver);
    }

    //########################################

    public function magentoStaticBlockChanged(Varien_Event_Observer $eventObserver)
    {
        $this->process('Magento_Cms_Block_SaveAfter', $eventObserver);
    }

    //########################################

    protected function process($observerModel, Varien_Event_Observer $eventObserver, $forceRun = false)
    {
        if (!$forceRun &&
            (Mage::helper('M2ePro/Module_Maintenance')->isEnabled() ||
             Mage::helper('M2ePro/Module')->isDisabled() ||
             !Mage::helper('M2ePro/Module')->isReadyToWork() ||
             !Mage::helper('M2ePro/Component')->getEnabledComponents())) {
            return;
        }

        try {

            /** @var Ess_M2ePro_Observer_Abstract $observer */
            $observer = Mage::getModel('M2ePro_Observer/'.$observerModel);
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
