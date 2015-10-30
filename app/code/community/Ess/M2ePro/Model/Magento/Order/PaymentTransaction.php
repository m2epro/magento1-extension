<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Magento_Order_PaymentTransaction extends Mage_Core_Model_Abstract
{
    /** @var $magentoOrder Mage_Sales_Model_Order */
    private $magentoOrder = NULL;

    /** @var $transaction Mage_Sales_Model_Order_Payment_Transaction */
    private $transaction = NULL;

    //########################################

    /**
     * @param Mage_Sales_Model_Order $magentoOrder
     * @return $this
     */
    public function setMagentoOrder(Mage_Sales_Model_Order $magentoOrder)
    {
        $this->magentoOrder = $magentoOrder;
        return $this;
    }

    //########################################

    public function getPaymentTransaction()
    {
        return $this->transaction;
    }

    //########################################

    public function buildPaymentTransaction()
    {
        if (version_compare(Mage::helper('M2ePro/Magento')->getVersion(false), '1.4.1', '<')) {
            return;
        }

        $payment = $this->magentoOrder->getPayment();

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
        $this->transaction = $payment->addTransaction($transactionType);

        if (@defined('Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS')) {
            $this->unsetData('transaction_id');
            $this->transaction->setAdditionalInformation(
                Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, $this->getData()
            );
        }

        $this->transaction->save();
    }

    //########################################
}