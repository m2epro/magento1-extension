<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Order_Proxy extends Ess_M2ePro_Model_Order_Proxy
{
    /** @var Ess_M2ePro_Model_Walmart_Order */
    protected $_order = null;

    /** @var Ess_M2ePro_Model_Walmart_Order_Item_Proxy[] */
    protected $_removedProxyItems = array();

    //########################################

    /**
     * @param Ess_M2ePro_Model_Order_Item_Proxy[] $items
     * @return Ess_M2ePro_Model_Order_Item_Proxy[]
     * @throws Exception
     */
    protected function mergeItems(array $items)
    {
        // Magento order can be created even it has zero price. Tested on Magento v. 1.7.0.2 and greater.
        // Doest not requires 'Zero Subtotal Checkout enabled'
        $minVersion = Mage::helper('M2ePro/Magento')->isCommunityEdition() ? '1.7.0.2' : '1.12';
        if (version_compare(Mage::helper('M2ePro/Magento')->getVersion(), $minVersion, '>=')) {
            return parent::mergeItems($items);
        }

        foreach ($items as $key => $item) {
            if ($item->getPrice() <= 0) {
                $this->_removedProxyItems[] = $item;
                unset($items[$key]);
            }
        }

        return parent::mergeItems($items);
    }

    //########################################

    /**
     * @return mixed
     */
    public function getChannelOrderNumber()
    {
        return $this->_order->getWalmartOrderId();
    }

    //########################################

    /**
     * @return array
     */
    public function getPaymentData()
    {
        $paymentData = array(
            'method'                => Mage::getSingleton('M2ePro/Magento_Payment')->getCode(),
            'component_mode'        => Ess_M2ePro_Helper_Component_Walmart::NICK,
            'payment_method'        => '',
            'channel_order_id'      => $this->_order->getWalmartOrderId(),
            'channel_final_fee'     => 0,
            'cash_on_delivery_cost' => 0,
            'transactions'          => array()
        );

        return $paymentData;
    }

    //########################################

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getShippingData()
    {
        $additionalData = '';

        if ($shippingDateTo = $this->_order->getShippingDateTo()) {
            $additionalData .= 'Ship By Date: '
                . Mage::helper('core')->formatDate($shippingDateTo, 'medium', true)
                . ' | ';
        }

        $shippingData = array(
            'carrier_title'   => $additionalData . Mage::helper('M2ePro')->__('Walmart Shipping'),
            'shipping_method' => $this->_order->getShippingService(),
            'shipping_price'  => $this->getBaseShippingPrice()
        );

        return $shippingData;
    }

    /**
     * @return float
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getShippingPrice()
    {
        $price = $this->_order->getShippingPrice();

        if ($this->isTaxModeNone() && $this->getShippingPriceTaxRate() > 0) {
            $price += $this->_order->getShippingPriceTaxAmount();
        }

        return $price;
    }

    //########################################

    /**
     * @return array
     */
    public function getChannelComments()
    {
        $comments = array();

        // Removed Order Items
        // ---------------------------------------
        if (!empty($this->_removedProxyItems)) {
            $comment = '<u>'.
                Mage::helper('M2ePro')->__(
                    'The following SKUs have zero price and can not be included in Magento order line items'
                ).
                ':</u><br/>';

            $zeroItems = array();
            foreach ($this->_removedProxyItems as $item) {
                $productSku = $item->getMagentoProduct()->getSku();
                $qtyPurchased = $item->getQty();

                $zeroItems[] = "<b>{$productSku}</b>: {$qtyPurchased} QTY";
            }

            $comments[] = $comment . implode('<br/>,', $zeroItems);
        }

        // ---------------------------------------

        return $comments;
    }

    //########################################

    /**
     * @return bool
     */
    public function hasTax()
    {
        return $this->_order->getProductPriceTaxRate() > 0;
    }

    /**
     * @return bool
     */
    public function isSalesTax()
    {
        return $this->hasTax();
    }

    /**
     * @return bool
     */
    public function isVatTax()
    {
        return false;
    }

    // ---------------------------------------

    /**
     * @return float|int
     */
    public function getProductPriceTaxRate()
    {
        return $this->_order->getProductPriceTaxRate();
    }

    /**
     * @return float|int
     */
    public function getShippingPriceTaxRate()
    {
        return $this->_order->getShippingPriceTaxRate();
    }

    //########################################
}
