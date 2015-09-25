<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Magento_Order_Shipment
{
    /** @var $magentoOrder Mage_Sales_Model_Order */
    private $magentoOrder = NULL;

    /** @var $shipment Mage_Sales_Model_Order_Shipment */
    private $shipment = NULL;

    // ########################################

    public function setMagentoOrder(Mage_Sales_Model_Order $magentoOrder)
    {
        $this->magentoOrder = $magentoOrder;

        return $this;
    }

    // ########################################

    public function getShipment()
    {
        return $this->shipment;
    }

    // ########################################

    public function buildShipment()
    {
        $this->prepareShipment();
        $this->magentoOrder->getShipmentsCollection()->addItem($this->shipment);
    }

    // ########################################

    protected function prepareShipment()
    {
        // Skip shipment observer
        // -----------------
        Mage::helper('M2ePro/Data_Global')->unsetValue('skip_shipment_observer');
        Mage::helper('M2ePro/Data_Global')->setValue('skip_shipment_observer', true);
        // -----------------

        $qtys = array();
        foreach ($this->magentoOrder->getAllItems() as $item) {
            $qtyToShip = $item->getQtyToShip();

            if ($qtyToShip == 0) {
                continue;
            }

            $qtys[$item->getId()] = $qtyToShip;
        }

        // Create shipment
        // -----------------
        $this->shipment = $this->magentoOrder->prepareShipment($qtys);
        $this->shipment->register();
        // it is necessary for updating qty_shipped field in sales_flat_order_item table
        $this->shipment->getOrder()->setIsInProcess(true);

        Mage::getModel('core/resource_transaction')
            ->addObject($this->shipment)
            ->addObject($this->shipment->getOrder())
            ->save();
        // -----------------
    }

    // ########################################
}