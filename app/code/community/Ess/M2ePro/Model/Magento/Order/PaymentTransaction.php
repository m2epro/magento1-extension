<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Order_PaymentTransaction extends Mage_Core_Model_Abstract
{
    /** @var $_magentoOrder Mage_Sales_Model_Order */
    protected $_magentoOrder = null;

    /** @var $_transaction Mage_Sales_Model_Order_Payment_Transaction */
    protected $_transaction = null;

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

    public function getPaymentTransaction()
    {
        return $this->_transaction;
    }

    //########################################

    public function buildPaymentTransaction()
    {
        if (version_compare(Mage::helper('M2ePro/Magento')->getVersion(false), '1.4.1', '<')) {
            return;
        }

        $payment = $this->_magentoOrder->getPayment();

        if ($payment === false) {
            return;
        }

        $transactionType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE;
        if ($this->getData('sum') < 0) {
            $transactionType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND;
        }

        $existTransaction = $payment->getTransaction($this->getData('transaction_id'));

        if ($existTransaction && $existTransaction->getTxnType() == $transactionType) {
            return NULL;
        }

        $payment->setTransactionId($this->getData('transaction_id'));
        $this->_transaction = $payment->addTransaction($transactionType);

        if (@defined('Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS')) {
            $this->unsetData('transaction_id');
            $this->_transaction->setAdditionalInformation(
                Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $this->getData()
            );
        }

        $this->_transaction->save();
    }

    //########################################
}
