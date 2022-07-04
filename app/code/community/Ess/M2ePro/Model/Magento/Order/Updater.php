<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Order_Updater
{
    /** @var $_magentoOrder Mage_Sales_Model_Order */
    protected $_magentoOrder = null;

    protected $_needSave = false;

    //########################################

    /**
     * Set magento order for updating
     *
     * @param Mage_Sales_Model_Order $order
     * @return Ess_M2ePro_Model_Magento_Order_Updater
     */
    public function setMagentoOrder(Mage_Sales_Model_Order $order)
    {
        $this->_magentoOrder = $order;

        return $this;
    }

    //########################################

    /**
     * @return Mage_Customer_Model_Customer
     */
    protected function getMagentoCustomer()
    {
        if ($this->_magentoOrder->getCustomerIsGuest()) {
            return null;
        }

        $customer = $this->_magentoOrder->getCustomer();
        if ($customer instanceof Varien_Object && $customer->getId()) {
            return $customer;
        }

        $customer = Mage::getModel('customer/customer')->load($this->_magentoOrder->getCustomerId());
        if ($customer->getId()) {
            $this->_magentoOrder->setCustomer($customer);
        }

        return $customer->getId() ? $customer : null;
    }

    //########################################

    /**
     * Update shipping address
     *
     * @param array $addressInfo
     */
    public function updateShippingAddress(array $addressInfo)
    {
        if ($this->_magentoOrder->isCanceled()) {
            return;
        }

        $shippingAddress = $this->_magentoOrder->getShippingAddress();
        if ($shippingAddress instanceof Mage_Sales_Model_Order_Address) {
            $shippingAddress->addData($addressInfo);
            $shippingAddress->implodeStreetAddress()->save();
        } else {
            /** @var $shippingAddress Mage_Sales_Model_Order_Address */
            $shippingAddress = Mage::getModel('sales/order_address');
            $shippingAddress->setCustomerId($this->_magentoOrder->getCustomerId());
            $shippingAddress->addData($addressInfo);
            $shippingAddress->implodeStreetAddress();

            // we need to set shipping address to order before address save to init parent_id field
            $this->_magentoOrder->setShippingAddress($shippingAddress);
            $shippingAddress->save();
        }

        // we need to save order to update data in table sales_flat_order_grid
        // setData method will force magento model to save entity
        $this->_magentoOrder->setForceUpdateGridRecords(false);
        $this->_needSave = true;
    }

    public function updateShippingDescription($shippingDescription)
    {
        $this->_magentoOrder->setData('shipping_description', $shippingDescription);
        $this->_needSave = true;
    }

    //########################################

    /**
     * Update customer email
     *
     * @param $email
     * @return null
     */
    public function updateCustomerEmail($email)
    {
        if ($this->_magentoOrder->isCanceled()) {
            return;
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return;
        }

        if ($this->_magentoOrder->getCustomerEmail() != $email) {
            $this->_magentoOrder->setCustomerEmail($email);
            $this->_needSave = true;
        }

        if (!$this->_magentoOrder->getCustomerIsGuest()) {
            $customer = $this->getMagentoCustomer();

            if ($customer === null) {
                return;
            }

            if (strpos($customer->getEmail(), Ess_M2ePro_Model_Magento_Customer::FAKE_EMAIL_POSTFIX) === false) {
                return;
            }

            $customer->setEmail($email)->save();
        }
    }

    /**
     * Update customer address
     *
     * @param array $customerAddress
     */
    public function updateCustomerAddress(array $customerAddress)
    {
        if ($this->_magentoOrder->isCanceled()) {
            return;
        }

        if ($this->_magentoOrder->getCustomerIsGuest()) {
            return;
        }

        $customer = $this->getMagentoCustomer();

        if ($customer === null) {
            return;
        }

        /** @var $customerAddress Mage_Customer_Model_Address */
        $customerAddress = Mage::getModel('customer/address')
            ->setData($customerAddress)
            ->setCustomerId($customer->getId())
            ->setIsDefaultBilling(false)
            ->setIsDefaultShipping(false);
        $customerAddress->implodeStreetAddress();
        $customerAddress->save();
    }

    //########################################

    /**
     * Update payment data (payment method, transactions, etc)
     *
     * @param array $newPaymentData
     */
    public function updatePaymentData(array $newPaymentData)
    {
        if ($this->_magentoOrder->isCanceled()) {
            return;
        }

        $payment = $this->_magentoOrder->getPayment();

        if ($payment instanceof Mage_Sales_Model_Order_Payment) {
            $payment->setAdditionalData(Mage::helper('M2ePro')->serialize($newPaymentData));
            $payment->save();
        }
    }

    //########################################

    /**
     * Add notes
     *
     * @param mixed $comments
     * @return null
     */
    public function updateComments($comments)
    {
        if ($this->_magentoOrder->isCanceled()) {
            return;
        }

        if (empty($comments)) {
            return;
        }

        !is_array($comments) && $comments = array($comments);

        $header = '<br/><b><u>' . Mage::helper('M2ePro')->__('M2E Pro Notes') . ':</u></b><br/><br/>';
        $comments = implode('<br/><br/>', $comments);

        $this->_magentoOrder->addStatusHistoryComment($header . $comments);
        $this->_needSave = true;
    }

    //########################################

    /**
     * Update status
     *
     * @param $status
     * @return null
     */
    public function updateStatus($status)
    {
        if ($this->_magentoOrder->isCanceled()) {
            return;
        }

        if ($status == '') {
            return;
        }

        if ($this->_magentoOrder->getState() == Mage_Sales_Model_Order::STATE_COMPLETE
            || $this->_magentoOrder->getState() == Mage_Sales_Model_Order::STATE_CLOSED
        ) {
            $this->_magentoOrder->setStatus($status);
        } else {
            $this->_magentoOrder->setState(Mage_Sales_Model_Order::STATE_PROCESSING, $status);
        }

        $this->_needSave = true;
    }

    //########################################

    public function cancel()
    {
        $this->_magentoOrder->setActionFlag(Mage_Sales_Model_Order::ACTION_FLAG_CANCEL, true);
        $this->_magentoOrder->setActionFlag(Mage_Sales_Model_Order::ACTION_FLAG_UNHOLD, true);

        if ($this->_magentoOrder->isCanceled()) {
            //throw new Ess_M2ePro_Model_Exception('Cancel is not allowed for Orders which were already Canceled.');
            return;
        }

        if ($this->_magentoOrder->hasCreditmemos()) {
            return;
        }

        if ($this->_magentoOrder->canUnhold()) {
            throw new Ess_M2ePro_Model_Exception('Cancel is not allowed for Orders which were put on Hold.');
        }

        if ($this->_magentoOrder->getState() === Mage_Sales_Model_Order::STATE_COMPLETE ||
            $this->_magentoOrder->getState() === Mage_Sales_Model_Order::STATE_CLOSED) {
            return;
        }

        $allInvoiced = true;
        foreach ($this->_magentoOrder->getAllItems() as $item) {
            if ($item->getQtyToInvoice()) {
                $allInvoiced = false;
                break;
            }
        }

        if ($allInvoiced) {
            throw new Ess_M2ePro_Model_Exception('Cancel is not allowed for Orders with Invoiced Items.');
        }

        $this->_magentoOrder->cancel()->save();
    }

    //########################################

    /**
     * Save magento order only once and only if it's needed
     */
    public function finishUpdate()
    {
        if ($this->_needSave) {
            $this->_magentoOrder->save();
        }
    }

    //########################################
}