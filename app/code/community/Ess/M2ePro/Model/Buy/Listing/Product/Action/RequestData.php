<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
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

    //########################################

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    // ---------------------------------------

    /**
     * @param Ess_M2ePro_Model_Listing_Product $object
     */
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

    //########################################

    /**
     * @return bool
     */
    public function hasSku()
    {
        return isset($this->data['sku']);
    }

    /**
     * @return bool
     */
    public function hasProductId()
    {
        return isset($this->data['product_id']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasQty()
    {
        return isset($this->data['qty']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasPrice()
    {
        return isset($this->data['price']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasCondition()
    {
        return isset($this->data['condition']);
    }

    /**
     * @return bool
     */
    public function hasConditionNote()
    {
        return isset($this->data['condition_note']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
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

    /**
     * @return bool
     */
    public function hasShippingStandardRate()
    {
        return isset($this->data['shipping_standard_rate']);
    }

    /**
     * @return bool
     */
    public function hasShippingExpeditedMode()
    {
        return isset($this->data['shipping_expedited_mode']);
    }

    /**
     * @return bool
     */
    public function hasShippingExpeditedRate()
    {
        return isset($this->data['shipping_expedited_rate']);
    }

    /**
     * @return bool
     */
    public function hasShippingOneDayMode()
    {
        return isset($this->data['shipping_one_day_mode']);
    }

    /**
     * @return bool
     */
    public function hasShippingOneDayRate()
    {
        return isset($this->data['shipping_one_day_rate']);
    }

    /**
     * @return bool
     */
    public function hasShippingTwoDayMode()
    {
        return isset($this->data['shipping_two_day_mode']);
    }

    /**
     * @return bool
     */
    public function hasShippingTwoDayRate()
    {
        return isset($this->data['shipping_two_day_rate']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasNewProductCoreData()
    {
        return isset($this->data['core']);
    }

    /**
     * @return bool
     */
    public function hasNewProductAttributesData()
    {
        return isset($this->data['attributes']);
    }

    //########################################

    public function getSku()
    {
        return $this->hasSku() ? $this->data['sku'] : NULL;
    }

    public function getProductId()
    {
        return $this->hasProductId() ? $this->data['product_id'] : NULL;
    }

    // ---------------------------------------

    public function getQty()
    {
        return $this->hasQty() ? $this->data['qty'] : NULL;
    }

    // ---------------------------------------

    public function getPrice()
    {
        return $this->hasPrice() ? $this->data['price'] : NULL;
    }

    // ---------------------------------------

    public function getCondition()
    {
        return $this->hasCondition() ? $this->data['condition'] : NULL;
    }

    public function getConditionNote()
    {
        return $this->hasConditionNote() ? $this->data['condition_note'] : NULL;
    }

    // ---------------------------------------

    public function getShippingStandardRate()
    {
        if (!$this->hasShippingStandardRate() || $this->data['shipping_standard_rate'] === '') {
            return NULL;
        }

        return $this->data['shipping_standard_rate'];
    }

    /**
     * @return int|null
     */
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

    /**
     * @return int|null
     */
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

    // ---------------------------------------

    public function getNewProductCoreData()
    {
        return $this->hasNewProductCoreData() ? $this->data['core'] : NULL;
    }

    public function getNewProductAttributesData()
    {
        return $this->hasNewProductAttributesData() ? $this->data['attributes'] : NULL;
    }

    //########################################
}