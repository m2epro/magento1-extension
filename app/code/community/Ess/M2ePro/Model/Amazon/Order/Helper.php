<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Order_Helper
{
    const AMAZON_STATUS_PENDING             = 'Pending';
    const AMAZON_STATUS_UNSHIPPED           = 'Unshipped';
    const AMAZON_STATUS_SHIPPED_PARTIALLY   = 'PartiallyShipped';
    const AMAZON_STATUS_SHIPPED             = 'Shipped';
    const AMAZON_STATUS_UNFULFILLABLE       = 'Unfulfillable';
    const AMAZON_STATUS_CANCELED            = 'Canceled';
    const AMAZON_STATUS_INVOICE_UNCONFIRMED = 'InvoiceUnconfirmed';

    // ########################################

    public function getStatus($amazonOrderStatus)
    {
        switch ($amazonOrderStatus) {
            case self::AMAZON_STATUS_UNSHIPPED:
                $status = Ess_M2ePro_Model_Amazon_Order::STATUS_UNSHIPPED;
                break;
            case self::AMAZON_STATUS_SHIPPED_PARTIALLY:
                $status = Ess_M2ePro_Model_Amazon_Order::STATUS_SHIPPED_PARTIALLY;
                break;
            case self::AMAZON_STATUS_SHIPPED:
                $status = Ess_M2ePro_Model_Amazon_Order::STATUS_SHIPPED;
                break;
            case self::AMAZON_STATUS_UNFULFILLABLE:
                $status = Ess_M2ePro_Model_Amazon_Order::STATUS_UNFULFILLABLE;
                break;
            case self::AMAZON_STATUS_CANCELED:
                $status = Ess_M2ePro_Model_Amazon_Order::STATUS_CANCELED;
                break;
            case self::AMAZON_STATUS_INVOICE_UNCONFIRMED:
                $status = Ess_M2ePro_Model_Amazon_Order::STATUS_INVOICE_UNCONFIRMED;
                break;
            case self::AMAZON_STATUS_PENDING:
            default:
                $status = Ess_M2ePro_Model_Amazon_Order::STATUS_PENDING;
                break;
        }

        return $status;
    }

    // ########################################
}