<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Magento_Order_Invoice
{
    /** @var $magentoOrder Mage_Sales_Model_Order */
    private $magentoOrder = NULL;

    /** @var $invoice Mage_Sales_Model_Order_Invoice */
    private $invoice = NULL;

    // ########################################

    public function setMagentoOrder(Mage_Sales_Model_Order $magentoOrder)
    {
        $this->magentoOrder = $magentoOrder;
        return $this;
    }

    // ########################################

    public function getInvoice()
    {
        return $this->invoice;
    }

    // ########################################

    public function buildInvoice()
    {
        $this->prepareInvoice();
    }

    // ########################################

    private function prepareInvoice()
    {
        // Skip invoice observer
        // -----------------
        Mage::helper('M2ePro/Data_Global')->unsetValue('skip_invoice_observer');
        Mage::helper('M2ePro/Data_Global')->setValue('skip_invoice_observer', true);
        // -----------------

        $qtys = array();
        foreach ($this->magentoOrder->getAllItems() as $item) {
            $qtyToInvoice = $item->getQtyToInvoice();

            if ($qtyToInvoice == 0) {
                continue;
            }

            $qtys[$item->getId()] = $item->getQtyToInvoice();
        }

        // Create invoice
        // -----------------
        $this->invoice = $this->magentoOrder->prepareInvoice($qtys);
        $this->invoice->register();
        // it is necessary for updating qty_invoiced field in sales_flat_order_item table
        $this->invoice->getOrder()->setIsInProcess(true);

        Mage::getModel('core/resource_transaction')
            ->addObject($this->invoice)
            ->addObject($this->invoice->getOrder())
            ->save();
        // -----------------
    }

    // ########################################
}