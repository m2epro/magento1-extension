<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Order_ExternalTransaction_Builder extends Mage_Core_Model_Abstract
{
    // ########################################

    public function initialize(array $data)
    {
        // Init general data
        // ------------------
        $this->setData('order_id', $data['order_id']);
        $this->setData('transaction_id', $data['transaction_id']);
        $this->setData('transaction_date', $data['transaction_date']);
        $this->setData('fee', (float)$data['fee']);
        $this->setData('sum', (float)$data['sum']);
        $this->setData('is_refund', (int)$data['is_refund']);
        // ------------------
    }

    // ########################################

    public function process()
    {
        return $this->createOrderExternalTransaction();
    }

    // ########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Order_ExternalTransaction
     */
    private function createOrderExternalTransaction()
    {
        $transaction = Mage::getModel('M2ePro/Ebay_Order_ExternalTransaction')->getCollection()
            ->addFieldToFilter('order_id', $this->getData('order_id'))
            ->addFieldToFilter('transaction_id', $this->getData('transaction_id'))
            ->getFirstItem();

        $transaction->addData($this->getData());
        $transaction->save();

        return $transaction;
    }

    // ########################################
}