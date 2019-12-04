<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Order_Item_Proxy
{
    /** @var Ess_M2ePro_Model_Ebay_Order_Item|Ess_M2ePro_Model_Amazon_Order_Item|Ess_M2ePro_Model_Walmart_Order_Item */
    protected $_item = null;

    protected $_qty = null;

    protected $_subtotal = null;

    protected $_additionalData = array();

    //########################################

    public function __construct(Ess_M2ePro_Model_Component_Child_Abstract $item)
    {
        $this->_item     = $item;
        $this->_subtotal = $this->getOriginalPrice() * $this->getOriginalQty();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Order_Proxy
     */
    public function getProxyOrder()
    {
        return $this->_item->getParentObject()->getOrder()->getProxy();
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Order_Item_Proxy $that
     * @return bool
     */
    public function equals(Ess_M2ePro_Model_Order_Item_Proxy $that)
    {
        if ($this->getProductId() === null || $that->getProductId() === null) {
            return false;
        }

        if ($this->getProductId() != $that->getProductId()) {
            return false;
        }

        $thisOptions = $this->getOptions();
        $thatOptions = $that->getOptions();

        $thisOptionsKeys = array_keys($thisOptions);
        $thatOptionsKeys = array_keys($thatOptions);

        $thisOptionsValues = array_values($thisOptions);
        $thatOptionsValues = array_values($thatOptions);

        $diffKeys = array_diff($thisOptionsKeys, $thatOptionsKeys);
        $diffValues = array_diff($thisOptionsValues, $thatOptionsValues);
        if (count($thisOptions) != count($thatOptions) || !empty($diffKeys) || !empty($diffValues)) {
            return false;
        }

        // grouped products have no options, that's why we have to compare associated products
        $thisAssociatedProducts = $this->getAssociatedProducts();
        $thatAssociatedProducts = $that->getAssociatedProducts();

        $diffProducts = array_diff($thisAssociatedProducts, $thatAssociatedProducts);
        if (count($thisAssociatedProducts) !== count($thatAssociatedProducts) || !empty($diffProducts)) {
            return false;
        }

        return true;
    }

    public function merge(Ess_M2ePro_Model_Order_Item_Proxy $that)
    {
        $this->setQty($this->getQty() + $that->getOriginalQty());
        $this->_subtotal += $that->getOriginalPrice() * $that->getOriginalQty();

        // merge additional data
        // ---------------------------------------
        $thisAdditionalData = $this->getAdditionalData();
        $thatAdditionalData = $that->getAdditionalData();

        $identifier = Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER;

        $thisAdditionalData[$identifier]['items'][] = $thatAdditionalData[$identifier]['items'][0];

        $this->_additionalData = $thisAdditionalData;
        // ---------------------------------------
    }

    //########################################

    public function getProduct()
    {
        return $this->_item->getParentObject()->getProduct();
    }

    public function getProductId()
    {
        return $this->_item->getParentObject()->getProductId();
    }

    public function getMagentoProduct()
    {
        return $this->_item->getParentObject()->getMagentoProduct();
    }

    //########################################

    public function getOptions()
    {
        return $this->_item->getParentObject()->getAssociatedOptions();
    }

    public function getAssociatedProducts()
    {
        return $this->_item->getParentObject()->getAssociatedProducts();
    }

    //########################################

    public function getBasePrice()
    {
        return $this->getProxyOrder()->convertPriceToBase($this->getPrice());
    }

    public function getPrice()
    {
        return $this->_subtotal / $this->getQty();
    }

    abstract public function getOriginalPrice();

    abstract public function getOriginalQty();

    public function setQty($qty)
    {
        if ((int)$qty <= 0) {
            throw new InvalidArgumentException('QTY cannot be less than zero.');
        }

        $this->_qty = (int)$qty;

        return $this;
    }

    public function getQty()
    {
        if ($this->_qty !== null) {
            return $this->_qty;
        }

        return $this->getOriginalQty();
    }

    //########################################

    public function hasTax()
    {
        return $this->getProxyOrder()->hasTax();
    }

    public function isSalesTax()
    {
        return $this->getProxyOrder()->isSalesTax();
    }

    public function isVatTax()
    {
        return $this->getProxyOrder()->isVatTax();
    }

    public function getTaxRate()
    {
        return $this->getProxyOrder()->getProductPriceTaxRate();
    }

    //########################################

    public function getWasteRecyclingFee()
    {
        return 0.0;
    }

    //########################################

    public function getGiftMessage()
    {
        return null;
    }

    //########################################

    abstract public function getAdditionalData();

    //########################################
}
