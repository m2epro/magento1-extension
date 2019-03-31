<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Order_Updater
{
    // M2ePro_TRANSLATIONS
    // Cancel is not allowed for Orders which were already Canceled.
    // Cancel is not allowed for Orders with Invoiced Items.
    // Cancel is not allowed for Orders which were put on Hold.
    // Cancel is not allowed for Orders which were Completed or Closed.

    //########################################

    /** @var $magentoOrder Mage_Sales_Model_Order */
    private $magentoOrder = NULL;

    private $needSave = false;

    //########################################

    /**
     * Set magento order for updating
     *
     * @param Mage_Sales_Model_Order $order
     * @return Ess_M2ePro_Model_Magento_Order_Updater
     */
    public function setMagentoOrder(Mage_Sales_Model_Order $order)
    {
        $this->magentoOrder = $order;

        return $this;
    }

    //########################################

    /**
     * @return Mage_Customer_Model_Customer
     */
    private function getMagentoCustomer()
    {
        if ($this->magentoOrder->getCustomerIsGuest()) {
            return null;
        }

        $customer = $this->magentoOrder->getCustomer();
        if ($customer instanceof Varien_Object && $customer->getId()) {
            return $customer;
        }

        $customer = Mage::getModel('customer/customer')->load($this->magentoOrder->getCustomerId());
        if ($customer->getId()) {
            $this->magentoOrder->setCustomer($customer);
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
        if ($this->magentoOrder->isCanceled()) {
            return;
        }

        $shippingAddress = $this->magentoOrder->getShippingAddress();
        if ($shippingAddress instanceof Mage_Sales_Model_Order_Address) {
            $shippingAddress->addData($addressInfo);
            $shippingAddress->implodeStreetAddress()->save();
        } else {
            /** @var $shippingAddress Mage_Sales_Model_Order_Address */
            $shippingAddress = Mage::getModel('sales/order_address');
            $shippingAddress->setCustomerId($this->magentoOrder->getCustomerId());
            $shippingAddress->addData($addressInfo);
            $shippingAddress->implodeStreetAddress();

            // we need to set shipping address to order before address save to init parent_id field
            $this->magentoOrder->setShippingAddress($shippingAddress);
            $shippingAddress->save();
        }

        // we need to save order to update data in table sales_flat_order_grid
        // setData method will force magento model to save entity
        $this->magentoOrder->setForceUpdateGridRecords(false);
        $this->needSave = true;
    }

    public function updateShippingDescription($shippingDescription)
    {
        $this->magentoOrder->setData('shipping_description', $shippingDescription);
        $this->needSave = true;
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
        if ($this->magentoOrder->isCanceled()) {
            return;
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return;
        }

        if ($this->magentoOrder->getCustomerEmail() != $email) {
            $this->magentoOrder->setCustomerEmail($email);
            $this->needSave = true;
        }

        if (!$this->magentoOrder->getCustomerIsGuest()) {
            $customer = $this->getMagentoCustomer();

            if (is_null($customer)) {
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
        if ($this->magentoOrder->isCanceled()) {
            return;
        }

        if ($this->magentoOrder->getCustomerIsGuest()) {
            return;
        }

        $customer = $this->getMagentoCustomer();

        if (is_null($customer)) {
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
        if ($this->magentoOrder->isCanceled()) {
            return;
        }

        $payment = $this->magentoOrder->getPayment();

        if ($payment instanceof Mage_Sales_Model_Order_Payment) {
            $payment->setAdditionalData(serialize($newPaymentData))->save();
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
        if ($this->magentoOrder->isCanceled()) {
            return;
        }

        if (empty($comments)) {
            return;
        }

        !is_array($comments) && $comments = array($comments);

        $header = '<br/><b><u>' . Mage::helper('M2ePro')->__('M2E Pro Notes') . ':</u></b><br/><br/>';
        $comments = implode('<br/><br/>', $comments);

        $this->magentoOrder->addStatusHistoryComment($header . $comments);
        $this->needSave = true;
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
        if ($this->magentoOrder->isCanceled()) {
            return;
        }

        if ($status == '') {
            return;
        }

        if ($this->magentoOrder->getState() == Mage_Sales_Model_Order::STATE_COMPLETE
            || $this->magentoOrder->getState() == Mage_Sales_Model_Order::STATE_CLOSED
        ) {
            $this->magentoOrder->setStatus($status);
        } else {
            $this->magentoOrder->setState(Mage_Sales_Model_Order::STATE_PROCESSING, $status);
        }

        $this->needSave = true;
    }

    //########################################

    public function cancel()
    {
        $this->magentoOrder->setActionFlag(Mage_Sales_Model_Order::ACTION_FLAG_CANCEL, true);
        $this->magentoOrder->setActionFlag(Mage_Sales_Model_Order::ACTION_FLAG_UNHOLD, true);

        if ($this->magentoOrder->isCanceled()) {
            //throw new Ess_M2ePro_Model_Exception('Cancel is not allowed for Orders which were already Canceled.');
            return;
        }

        if ($this->magentoOrder->canUnhold()) {
            throw new Ess_M2ePro_Model_Exception('Cancel is not allowed for Orders which were put on Hold.');
        }

        if ($this->magentoOrder->getState() === Mage_Sales_Model_Order::STATE_COMPLETE ||
            $this->magentoOrder->getState() === Mage_Sales_Model_Order::STATE_CLOSED) {
            throw new Ess_M2ePro_Model_Exception('Cancel is not allowed for Orders which were Completed or Closed.');
        }

        $allInvoiced = true;
        foreach ($this->magentoOrder->getAllItems() as $item) {
            if ($item->getQtyToInvoice()) {
                $allInvoiced = false;
                break;
            }
        }
        if ($allInvoiced) {
            throw new Ess_M2ePro_Model_Exception('Cancel is not allowed for Orders with Invoiced Items.');
        }

        $this->magentoOrder->cancel()->save();
    }

    //########################################

    /**
     * Save magento order only once and only if it's needed
     */
    public function finishUpdate()
    {
        if ($this->needSave) {
            $this->magentoOrder->save();
        }
    }

    //########################################
}