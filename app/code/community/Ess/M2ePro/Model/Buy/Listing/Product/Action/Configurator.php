<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_Configurator
    extends Ess_M2ePro_Model_Listing_Product_Action_Configurator
{
    const DATA_TYPE_QTY          = 'qty';
    const DATA_TYPE_PRICE        = 'price';
    const DATA_TYPE_DETAILS      = 'details';
    const DATA_TYPE_SHIPPING     = 'shipping';
    const DATA_TYPE_NEW_PRODUCT  = 'new_product';

    //########################################

    /**
     * @return array
     */
    public function getAllDataTypes()
    {
        return array(
            self::DATA_TYPE_QTY,
            self::DATA_TYPE_PRICE,
            self::DATA_TYPE_DETAILS,
            self::DATA_TYPE_SHIPPING,
            self::DATA_TYPE_NEW_PRODUCT,
        );
    }

    //########################################

    /**
     * @return bool
     */
    public function isQtyAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_QTY);
    }

    /**
     * @return $this
     */
    public function allowQty()
    {
        return $this->allow(self::DATA_TYPE_QTY);
    }

    /**
     * @return $this
     */
    public function disallowQty()
    {
        return $this->disallow(self::DATA_TYPE_QTY);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isPriceAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_PRICE);
    }

    /**
     * @return $this
     */
    public function allowPrice()
    {
        return $this->allow(self::DATA_TYPE_PRICE);
    }

    /**
     * @return $this
     */
    public function disallowPrice()
    {
        return $this->disallow(self::DATA_TYPE_PRICE);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isDetailsAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_DETAILS);
    }

    /**
     * @return $this
     */
    public function allowDetails()
    {
        return $this->allow(self::DATA_TYPE_DETAILS);
    }

    /**
     * @return $this
     */
    public function disallowDetails()
    {
        return $this->disallow(self::DATA_TYPE_DETAILS);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isShippingAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_SHIPPING);
    }

    /**
     * @return $this
     */
    public function allowShipping()
    {
        return $this->allow(self::DATA_TYPE_SHIPPING);
    }

    /**
     * @return $this
     */
    public function disallowShipping()
    {
        return $this->disallow(self::DATA_TYPE_SHIPPING);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isNewProductAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_NEW_PRODUCT);
    }

    /**
     * @return $this
     */
    public function allowNewProduct()
    {
        return $this->allow(self::DATA_TYPE_NEW_PRODUCT);
    }

    /**
     * @return $this
     */
    public function disallowNewProduct()
    {
        return $this->disallow(self::DATA_TYPE_NEW_PRODUCT);
    }

    //########################################
}