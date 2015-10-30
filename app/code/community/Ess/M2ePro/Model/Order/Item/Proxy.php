<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Order_Item_Proxy
{
    /** @var Ess_M2ePro_Model_Ebay_Order_Item|Ess_M2ePro_Model_Amazon_Order_Item|
     * Ess_M2ePro_Model_Buy_Order_Item */
    protected $item = NULL;

    protected $qty = NULL;

    protected $subtotal = NULL;

    protected $additionalData = array();

    //########################################

    public function __construct(Ess_M2ePro_Model_Component_Child_Abstract $item)
    {
        $this->item = $item;
        $this->subtotal = $this->getOriginalPrice() * $this->getOriginalQty();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Order_Proxy
     */
    public function getProxyOrder()
    {
        return $this->item->getParentObject()->getOrder()->getProxy();
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Order_Item_Proxy $that
     * @return bool
     */
    public function equals(Ess_M2ePro_Model_Order_Item_Proxy $that)
    {
        if (is_null($this->getProductId()) || is_null($that->getProductId())) {
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

        if (count($thisOptions) != count($thatOptions)
            || count(array_diff($thisOptionsKeys, $thatOptionsKeys)) > 0
            || count(array_diff($thisOptionsValues, $thatOptionsValues)) > 0
        ) {
            return false;
        }

        // grouped products have no options, that's why we have to compare associated products
        $thisAssociatedProducts = $this->getAssociatedProducts();
        $thatAssociatedProducts = $that->getAssociatedProducts();

        if (count($thisAssociatedProducts) != count($thatAssociatedProducts)
            || count(array_diff($thisAssociatedProducts, $thatAssociatedProducts)) > 0
        ) {
            return false;
        }

        return true;
    }

    public function merge(Ess_M2ePro_Model_Order_Item_Proxy $that)
    {
        $this->setQty($this->getQty() + $that->getOriginalQty());
        $this->subtotal += $that->getOriginalPrice() * $that->getOriginalQty();

        // merge additional data
        // ---------------------------------------
        $thisAdditionalData = $this->getAdditionalData();
        $thatAdditionalData = $that->getAdditionalData();

        $identifier = Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER;

        $thisAdditionalData[$identifier]['items'][] = $thatAdditionalData[$identifier]['items'][0];

        $this->additionalData = $thisAdditionalData;
        // ---------------------------------------
    }

    //########################################

    public function getProduct()
    {
        return $this->item->getParentObject()->getProduct();
    }

    public function getProductId()
    {
        return $this->item->getParentObject()->getProductId();
    }

    public function getMagentoProduct()
    {
        return $this->item->getParentObject()->getMagentoProduct();
    }

    //########################################

    public function getOptions()
    {
        return $this->item->getParentObject()->getAssociatedOptions();
    }

    public function getAssociatedProducts()
    {
        return $this->item->getParentObject()->getAssociatedProducts();
    }

    //########################################

    public function getBasePrice()
    {
        return $this->getProxyOrder()->convertPriceToBase($this->getPrice());
    }

    public function getPrice()
    {
        return $this->subtotal / $this->getQty();
    }

    abstract public function getOriginalPrice();

    abstract public function getOriginalQty();

    public function setQty($qty)
    {
        if ((int)$qty <= 0) {
            throw new InvalidArgumentException('QTY cannot be less than zero.');
        }

        $this->qty = (int)$qty;

        return $this;
    }

    public function getQty()
    {
        if (!is_null($this->qty)) {
            return $this->qty;
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

    public function getGiftMessage()
    {
        return null;
    }

    //########################################

    abstract public function getAdditionalData();

    //########################################
}