<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Order_Invoice
{
    /** @var $_magentoOrder Mage_Sales_Model_Order */
    protected $_magentoOrder = null;

    /** @var $_invoice Mage_Sales_Model_Order_Invoice */
    protected $_invoice = null;

    //########################################

    /**
     * @param Mage_Sales_Model_Order $magentoOrder
     * @return $this
     */
    public function setMagentoOrder(Mage_Sales_Model_Order $magentoOrder)
    {
        $this->_magentoOrder = $magentoOrder;
        return $this;
    }

    //########################################

    public function getInvoice()
    {
        return $this->_invoice;
    }

    //########################################

    public function buildInvoice()
    {
        $this->prepareInvoice();
    }

    //########################################

    protected function prepareInvoice()
    {
        // Skip invoice observer
        // ---------------------------------------
        Mage::helper('M2ePro/Data_Global')->unsetValue('skip_invoice_observer');
        Mage::helper('M2ePro/Data_Global')->setValue('skip_invoice_observer', true);
        // ---------------------------------------

        $qtys = array();
        foreach ($this->_magentoOrder->getAllItems() as $item) {
            $qtyToInvoice = $item->getQtyToInvoice();

            if ($qtyToInvoice == 0) {
                continue;
            }

            $qtys[$item->getId()] = $item->getQtyToInvoice();
        }

        // Create invoice
        // ---------------------------------------
        $this->_invoice = $this->_magentoOrder->prepareInvoice($qtys);
        $this->_invoice->register();
        // it is necessary for updating qty_invoiced field in sales_flat_order_item table
        $this->_invoice->getOrder()->setIsInProcess(true);

        Mage::getModel('core/resource_transaction')
            ->addObject($this->_invoice)
            ->addObject($this->_invoice->getOrder())
            ->save();
        // ---------------------------------------
    }

    //########################################
}
