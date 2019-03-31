<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Order_Helper
{
    const EBAY_ORDER_STATUS_ACTIVE    = 'Active';
    const EBAY_ORDER_STATUS_COMPLETED = 'Completed';
    const EBAY_ORDER_STATUS_CANCELLED = 'Cancelled';
    const EBAY_ORDER_STATUS_INACTIVE  = 'Inactive';

    const EBAY_CHECKOUT_STATUS_COMPLETE = 'Complete';

    const EBAY_PAYMENT_METHOD_NONE      = 'None';
    const EBAY_PAYMENT_STATUS_SUCCEEDED = 'NoPaymentFailure';

    //########################################

    public function getOrderStatus($orderStatusEbay)
    {
        $orderStatus = null;

        switch ($orderStatusEbay) {
            case self::EBAY_ORDER_STATUS_ACTIVE:
                $orderStatus = Ess_M2ePro_Model_Ebay_Order::ORDER_STATUS_ACTIVE;
                break;

            case self::EBAY_ORDER_STATUS_COMPLETED:
                $orderStatus = Ess_M2ePro_Model_Ebay_Order::ORDER_STATUS_COMPLETED;
                break;

            case self::EBAY_ORDER_STATUS_CANCELLED:
                $orderStatus = Ess_M2ePro_Model_Ebay_Order::ORDER_STATUS_CANCELLED;
                break;

            case self::EBAY_ORDER_STATUS_INACTIVE:
                $orderStatus = Ess_M2ePro_Model_Ebay_Order::ORDER_STATUS_INACTIVE;
                break;
        }

        return $orderStatus;
    }

    //########################################

    public function getCheckoutStatus($checkoutStatusEbay)
    {
        if ($checkoutStatusEbay == self::EBAY_CHECKOUT_STATUS_COMPLETE) {
            return Ess_M2ePro_Model_Ebay_Order::CHECKOUT_STATUS_COMPLETED;
        }

        return Ess_M2ePro_Model_Ebay_Order::CHECKOUT_STATUS_INCOMPLETE;
    }

    public function getPaymentStatus($paymentMethod, $paymentDate, $paymentStatusEbay)
    {
        if ($paymentMethod == self::EBAY_PAYMENT_METHOD_NONE) {

            if ($paymentDate) {
                return Ess_M2ePro_Model_Ebay_Order::PAYMENT_STATUS_COMPLETED;
            }

            if ($paymentStatusEbay == self::EBAY_PAYMENT_STATUS_SUCCEEDED) {
                return Ess_M2ePro_Model_Ebay_Order::PAYMENT_STATUS_NOT_SELECTED;
            }

        } else {

            if ($paymentStatusEbay == self::EBAY_PAYMENT_STATUS_SUCCEEDED) {
                return $paymentDate
                    ? Ess_M2ePro_Model_Ebay_Order::PAYMENT_STATUS_COMPLETED
                    : Ess_M2ePro_Model_Ebay_Order::PAYMENT_STATUS_PROCESS;
            }
        }

        return Ess_M2ePro_Model_Ebay_Order::PAYMENT_STATUS_ERROR;
    }

    public function getShippingStatus($shippingDate, $isShippingServiceSelected)
    {
        if ($shippingDate == '') {
            return $isShippingServiceSelected
                ? Ess_M2ePro_Model_Ebay_Order::SHIPPING_STATUS_PROCESSING
                : Ess_M2ePro_Model_Ebay_Order::SHIPPING_STATUS_NOT_SELECTED;
        }

        return Ess_M2ePro_Model_Ebay_Order::SHIPPING_STATUS_COMPLETED;
    }

    //########################################

    public function getPaymentMethodNameByCode($code, $marketplaceId)
    {
        if ((int)$marketplaceId <= 0) {
            return $code;
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableDictMarketplace = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_dictionary_marketplace');

        $dbSelect = $connRead->select()
            ->from($tableDictMarketplace, 'payments')
            ->where('`marketplace_id` = ?', (int)$marketplaceId);
        $marketplace = $connRead->fetchRow($dbSelect);

        if (!$marketplace) {
            return $code;
        }

        $payments = (array)Mage::helper('M2ePro')->jsonDecode($marketplace['payments']);

        foreach ($payments as $payment) {
            if ($payment['ebay_id'] == $code) {
                return $payment['title'];
            }
        }

        return $code;
    }

    public function getShippingServiceNameByCode($code, $marketplaceId)
    {
        if ((int)$marketplaceId <= 0) {
            return $code;
        }

        /** @var $connRead Varien_Db_Adapter_Pdo_Mysql */
        $connRead          = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableDictShipping = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_ebay_dictionary_shipping');

        $dbSelect = $connRead->select()
            ->from($tableDictShipping, 'title')
            ->where('`marketplace_id` = ?', (int)$marketplaceId)
            ->where('`ebay_id` = ?', $code);
        $shipping = $connRead->fetchRow($dbSelect);

        return !empty($shipping['title']) ? $shipping['title'] : $code;
    }

    //########################################
}