<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Configurator
    extends Ess_M2ePro_Model_Listing_Product_Action_Configurator
{
    const DATA_TYPE_QTY     = 'qty';
    const DATA_TYPE_PRICE   = 'price';
    const DATA_TYPE_IMAGES  = 'images';
    const DATA_TYPE_DETAILS = 'details';
    const DATA_TYPE_SHIPPING_OVERRIDE = 'shipping_override';

    // ########################################

    public function getAllDataTypes()
    {
        return array(
            self::DATA_TYPE_QTY,
            self::DATA_TYPE_PRICE,
            self::DATA_TYPE_DETAILS,
            self::DATA_TYPE_IMAGES,
            self::DATA_TYPE_SHIPPING_OVERRIDE
        );
    }

    // ########################################

    public function isQtyAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_QTY);
    }

    public function allowQty()
    {
        return $this->allow(self::DATA_TYPE_QTY);
    }

    public function disallowQty()
    {
        return $this->disallow(self::DATA_TYPE_QTY);
    }

    // ----------------------------------------

    public function isPriceAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_PRICE);
    }

    public function allowPrice()
    {
        return $this->allow(self::DATA_TYPE_PRICE);
    }

    public function disallowPrice()
    {
        return $this->disallow(self::DATA_TYPE_PRICE);
    }

    // ----------------------------------------

    public function isDetailsAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_DETAILS);
    }

    public function allowDetails()
    {
        return $this->allow(self::DATA_TYPE_DETAILS);
    }

    public function disallowDetails()
    {
        return $this->disallow(self::DATA_TYPE_DETAILS);
    }

    // ----------------------------------------

    public function isImagesAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_IMAGES);
    }

    public function allowImages()
    {
        return $this->allow(self::DATA_TYPE_IMAGES);
    }

    public function disallowImages()
    {
        return $this->disallow(self::DATA_TYPE_IMAGES);
    }

    // ----------------------------------------

    public function isShippingOverrideAllowed()
    {
        return $this->isAllowed(self::DATA_TYPE_SHIPPING_OVERRIDE);
    }

    public function allowShippingOverride()
    {
        return $this->allow(self::DATA_TYPE_SHIPPING_OVERRIDE);
    }

    public function disallowShippingOverride()
    {
        return $this->disallow(self::DATA_TYPE_SHIPPING_OVERRIDE);
    }

    // ########################################
}