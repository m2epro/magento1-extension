<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_RequestData
    extends Ess_M2ePro_Model_Listing_Product_Action_RequestData
{
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

    /**
     * @return bool
     */
    public function hasProductIdType()
    {
        return isset($this->data['product_id_type']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasTypeMode()
    {
        return isset($this->data['type_mode']);
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
    public function hasHandlingTime()
    {
        return isset($this->data['handling_time']);
    }

    /**
     * @return bool
     */
    public function hasRestockDate()
    {
        return isset($this->data['restock_date']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasRegularPrice()
    {
        return isset($this->data['price']);
    }

    /**
     * @return bool
     */
    public function hasRegularSalePrice()
    {
        return isset($this->data['sale_price']);
    }

    // ---------------------------------------

    public function hasBusinessPrice()
    {
        return isset($this->data['business_price']);
    }

    public function hasBusinessDiscounts()
    {
        return isset($this->data['business_discounts']);
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
    public function hasGiftWrap()
    {
        return isset($this->data['gift_wrap']);
    }

    /**
     * @return bool
     */
    public function hasGiftMessage()
    {
        return isset($this->data['gift_message']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasShippingData()
    {
        return isset($this->data['shipping_data']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasTaxCode()
    {
        return isset($this->data['tax_code']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasNumberOfItems()
    {
        return isset($this->data['number_of_items']);
    }

    /**
     * @return bool
     */
    public function hasItemPackageQuantity()
    {
        return isset($this->data['item_package_quantity']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasBrowsenodeId()
    {
        return isset($this->data['browsenode_id']);
    }

    /**
     * @return bool
     */
    public function hasProductDataNick()
    {
        return isset($this->data['product_data_nick']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasProductData()
    {
        return isset($this->data['product_data']);
    }

    /**
     * @return bool
     */
    public function hasDescriptionData()
    {
        return isset($this->data['description_data']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasImagesData()
    {
        return isset($this->data['images_data']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasVariationAttributes()
    {
        return isset($this->data['variation_data']['attributes']);
    }

    //########################################

    /**
     * @return string|null
     */
    public function getSku()
    {
        return $this->hasSku() ? $this->data['sku'] : NULL;
    }

    /**
     * @return int|null
     */
    public function getProductId()
    {
        return $this->hasProductId() ? $this->data['product_id'] : NULL;
    }

    /**
     * @return string|null
     */
    public function getProductIdType()
    {
        return $this->hasProductIdType() ? $this->data['product_id_type'] : NULL;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isTypeModeExist()
    {
        if (!$this->hasTypeMode()) {
            return false;
        }

        return $this->data['type_mode']
            == Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Request::LIST_TYPE_EXIST;
    }

    /**
     * @return bool
     */
    public function isTypeModeNew()
    {
        if (!$this->hasTypeMode()) {
            return false;
        }

        $listTypeNew = Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Request::LIST_TYPE_NEW;

        return $this->data['type_mode'] == $listTypeNew;
    }

    // ---------------------------------------

    /**
     * @return int|null
     */
    public function getQty()
    {
        return $this->hasQty() ? $this->data['qty'] : NULL;
    }

    // ---------------------------------------

    /**
     * @return string|null
     */
    public function getHandlingTime()
    {
        return $this->hasHandlingTime() ? $this->data['handling_time'] : NULL;
    }

    /**
     * @return bool|null
     */
    public function getRestockDate()
    {
        return $this->hasRestockDate() ? $this->data['restock_date'] : NULL;
    }

    // ---------------------------------------

    /**
     * @return float|null
     */
    public function getRegularPrice()
    {
        return $this->hasRegularPrice() ? $this->data['price'] : NULL;
    }

    /**
     * @return float|null
     */
    public function getRegularSalePrice()
    {
        return $this->hasRegularSalePrice() ? $this->data['sale_price'] : NULL;
    }

    /**
     * @return string|null
     */
    public function getRegularSalePriceStartDate()
    {
        return $this->hasRegularSalePrice() ? $this->data['sale_price_start_date'] : NULL;
    }

    /**
     * @return string|null
     */
    public function getRegularSalePriceEndDate()
    {
        return $this->hasRegularSalePrice() ? $this->data['sale_price_end_date'] : NULL;
    }

    // ---------------------------------------

    /**
     * @return float|null
     */
    public function getBusinessPrice()
    {
        return $this->hasBusinessPrice() ? $this->data['business_price'] : NULL;
    }

    /**
     * @return array|null
     */
    public function getBusinessDiscounts()
    {
        return $this->hasBusinessDiscounts() ? $this->data['business_discounts'] : NULL;
    }

    // ---------------------------------------

    /**
     * @return string|null
     */
    public function getCondition()
    {
        return $this->hasCondition() ? $this->data['condition'] : NULL;
    }

    /**
     * @return string|null
     */
    public function getConditionNote()
    {
        return $this->hasConditionNote() ? $this->data['condition_note'] : NULL;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function getGiftWrap()
    {
        return $this->hasGiftWrap() ? $this->data['gift_wrap'] : NULL;
    }

    /**
     * @return bool
     */
    public function getGiftMessage()
    {
        return $this->hasGiftMessage() ? $this->data['gift_message'] : NULL;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function getShippingData()
    {
        return $this->hasShippingData() ? $this->data['shipping_data'] : NULL;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function getTaxCode()
    {
        return $this->hasTaxCode() ? $this->data['tax_code'] : NULL;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function getNumberOfItems()
    {
        return $this->hasNumberOfItems() ? $this->data['number_of_items'] : NULL;
    }

    /**
     * @return bool
     */
    public function getItemPackageQuantity()
    {
        return $this->hasItemPackageQuantity() ? $this->data['item_package_quantity'] : NULL;
    }

    // ---------------------------------------

    /**
     * @return float|null
     */
    public function getBrowsenodeId()
    {
        return $this->hasBrowsenodeId() ? $this->data['browsenode_id'] : NULL;
    }

    /**
     * @return string|null
     */
    public function getProductDataNick()
    {
        return $this->hasProductDataNick() ? $this->data['product_data_nick'] : NULL;
    }

    // ---------------------------------------

    /**
     * @return string|null
     */
    public function getProductData()
    {
        return $this->hasProductData() ? $this->data['product_data'] : NULL;
    }

    /**
     * @return string|null
     */
    public function getDescriptionData()
    {
        return $this->hasDescriptionData() ? $this->data['description_data'] : NULL;
    }

    // ---------------------------------------

    /**
     * @return mixed
     */
    public function getImagesData()
    {
        return $this->hasImagesData() ? $this->data['images_data'] : NULL;
    }

    // ---------------------------------------

    /**
     * @return mixed
     */
    public function getVariationAttributes()
    {
        return $this->hasVariationAttributes() ? $this->data['variation_data']['attributes'] : NULL;
    }

    //########################################
}