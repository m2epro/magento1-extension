<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Order_Shipment
{
    /** @var $_magentoOrder Mage_Sales_Model_Order */
    protected $_magentoOrder = null;

    /** @var $_shipment Mage_Sales_Model_Order_Shipment */
    protected $_shipment = null;

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

    public function getShipment()
    {
        return $this->_shipment;
    }

    //########################################

    public function buildShipment()
    {
        $this->prepareShipment();
        $this->_magentoOrder->getShipmentsCollection()->addItem($this->_shipment);
    }

    //########################################

    protected function prepareShipment()
    {
        // Skip shipment observer
        // ---------------------------------------
        Mage::helper('M2ePro/Data_Global')->unsetValue('skip_shipment_observer');
        Mage::helper('M2ePro/Data_Global')->setValue('skip_shipment_observer', true);
        // ---------------------------------------

        $qtys = array();
        foreach ($this->_magentoOrder->getAllItems() as $item) {
            $qtyToShip = $item->getQtyToShip();

            if ($qtyToShip == 0) {
                continue;
            }

            $qtys[$item->getId()] = $qtyToShip;
        }

        // Create shipment
        // ---------------------------------------
        $this->_shipment = $this->_magentoOrder->prepareShipment($qtys);
        $this->_shipment->register();
        // it is necessary for updating qty_shipped field in sales_flat_order_item table
        $this->_shipment->getOrder()->setIsInProcess(true);

        Mage::getModel('core/resource_transaction')
            ->addObject($this->_shipment)
            ->addObject($this->_shipment->getOrder())
            ->save();
        // ---------------------------------------
    }

    //########################################
}
