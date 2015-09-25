<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Buy_Listing_Product_Action_RequestData
{
    /**
     * @var array
     */
    private $data = array();

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    private $listingProduct = NULL;

    // ########################################

    public function setData(array $data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    // ----------------------------------------

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $object)
    {
        $this->listingProduct = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        return $this->listingProduct;
    }

    // ########################################

    public function hasSku()
    {
        return isset($this->data['sku']);
    }

    public function hasProductId()
    {
        return isset($this->data['product_id']);
    }

    public function hasProductIdType()
    {
        return isset($this->data['product_id_type']);
    }

    // ----------------------------------------

    public function hasQty()
    {
        return isset($this->data['qty']);
    }

    // ----------------------------------------

    public function hasPrice()
    {
        return isset($this->data['price']);
    }

    // ----------------------------------------

    public function hasCondition()
    {
        return isset($this->data['condition']);
    }

    public function hasConditionNote()
    {
        return isset($this->data['condition_note']);
    }

    // ----------------------------------------

    public function hasShippingData()
    {
        return $this->hasShippingStandardRate()  ||
               $this->hasShippingExpeditedMode() ||
               $this->hasShippingExpeditedRate() ||
               $this->hasShippingOneDayMode()    ||
               $this->hasShippingOneDayRate()    ||
               $this->hasShippingTwoDayMode()    ||
               $this->hasShippingTwoDayRate();

    }

    public function hasShippingStandardRate()
    {
        return isset($this->data['shipping_standard_rate']);
    }

    public function hasShippingExpeditedMode()
    {
        return isset($this->data['shipping_expedited_mode']);
    }

    public function hasShippingExpeditedRate()
    {
        return isset($this->data['shipping_expedited_rate']);
    }

    public function hasShippingOneDayMode()
    {
        return isset($this->data['shipping_one_day_mode']);
    }

    public function hasShippingOneDayRate()
    {
        return isset($this->data['shipping_one_day_rate']);
    }

    public function hasShippingTwoDayMode()
    {
        return isset($this->data['shipping_two_day_mode']);
    }

    public function hasShippingTwoDayRate()
    {
        return isset($this->data['shipping_two_day_rate']);
    }

    // ----------------------------------------

    public function hasNewProductCoreData()
    {
        return isset($this->data['core']);
    }

    public function hasNewProductAttributesData()
    {
        return isset($this->data['attributes']);
    }

    // ########################################

    public function getSku()
    {
        return $this->hasSku() ? $this->data['sku'] : NULL;
    }

    public function getProductId()
    {
        return $this->hasProductId() ? $this->data['product_id'] : NULL;
    }

    public function getProductIdType()
    {
        return $this->hasProductIdType() ? $this->data['product_id_type'] : NULL;
    }

    // ----------------------------------------

    public function getQty()
    {
        return $this->hasQty() ? $this->data['qty'] : NULL;
    }

    // ----------------------------------------

    public function getPrice()
    {
        return $this->hasPrice() ? $this->data['price'] : NULL;
    }

    // ----------------------------------------

    public function getCondition()
    {
        return $this->hasCondition() ? $this->data['condition'] : NULL;
    }

    public function getConditionNote()
    {
        return $this->hasConditionNote() ? $this->data['condition_note'] : NULL;
    }

    // ----------------------------------------

    public function getShippingStandardRate()
    {
        if (!$this->hasShippingStandardRate() || $this->data['shipping_standard_rate'] === '') {
            return NULL;
        }

        return $this->data['shipping_standard_rate'];
    }

    public function getShippingExpeditedMode()
    {
        return $this->hasShippingExpeditedMode() ? (int)$this->data['shipping_expedited_mode'] : NULL;
    }

    public function getShippingExpeditedRate()
    {
        if (!$this->hasShippingExpeditedRate() || $this->data['shipping_expedited_rate'] === '') {
            return NULL;
        }

        return $this->data['shipping_expedited_rate'];
    }

    public function getShippingOneDayMode()
    {
        return $this->hasShippingOneDayMode() ? (int)$this->data['shipping_one_day_mode'] : NULL;
    }

    public function getShippingOneDayRate()
    {
        if (!$this->hasShippingOneDayRate() || $this->data['shipping_one_day_rate'] === '') {
            return NULL;
        }

        return $this->data['shipping_one_day_rate'];
    }

    public function getShippingTwoDayMode()
    {
        return $this->hasShippingTwoDayMode() ? (int)$this->data['shipping_two_day_mode'] : NULL;
    }

    public function getShippingTwoDayRate()
    {
        if (!$this->hasShippingTwoDayRate() || $this->data['shipping_two_day_rate'] === '') {
            return NULL;
        }

        return $this->data['shipping_two_day_rate'];
    }

    // ----------------------------------------

    public function getNewProductCoreData()
    {
        return $this->hasNewProductCoreData() ? $this->data['core'] : NULL;
    }

    public function getNewProductAttributesData()
    {
        return $this->hasNewProductAttributesData() ? $this->data['attributes'] : NULL;
    }

    // ########################################
}