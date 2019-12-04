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
        return isset($this->_data['sku']);
    }

    /**
     * @return bool
     */
    public function hasProductId()
    {
        return isset($this->_data['product_id']);
    }

    /**
     * @return bool
     */
    public function hasProductIdType()
    {
        return isset($this->_data['product_id_type']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasTypeMode()
    {
        return isset($this->_data['type_mode']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasQty()
    {
        return isset($this->_data['qty']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasHandlingTime()
    {
        return isset($this->_data['handling_time']);
    }

    /**
     * @return bool
     */
    public function hasRestockDate()
    {
        return isset($this->_data['restock_date']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasRegularPrice()
    {
        return isset($this->_data['price']);
    }

    /**
     * @return bool
     */
    public function hasRegularSalePrice()
    {
        return isset($this->_data['sale_price']);
    }

    // ---------------------------------------

    public function hasBusinessPrice()
    {
        return isset($this->_data['business_price']);
    }

    public function hasBusinessDiscounts()
    {
        return isset($this->_data['business_discounts']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasCondition()
    {
        return isset($this->_data['condition']);
    }

    /**
     * @return bool
     */
    public function hasConditionNote()
    {
        return isset($this->_data['condition_note']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasGiftWrap()
    {
        return isset($this->_data['gift_wrap']);
    }

    /**
     * @return bool
     */
    public function hasGiftMessage()
    {
        return isset($this->_data['gift_message']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasShippingData()
    {
        return isset($this->_data['shipping_data']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasTaxCode()
    {
        return isset($this->_data['tax_code']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasNumberOfItems()
    {
        return isset($this->_data['number_of_items']);
    }

    /**
     * @return bool
     */
    public function hasItemPackageQuantity()
    {
        return isset($this->_data['item_package_quantity']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasBrowsenodeId()
    {
        return isset($this->_data['browsenode_id']);
    }

    /**
     * @return bool
     */
    public function hasProductDataNick()
    {
        return isset($this->_data['product_data_nick']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasProductData()
    {
        return isset($this->_data['product_data']);
    }

    /**
     * @return bool
     */
    public function hasDescriptionData()
    {
        return isset($this->_data['description_data']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasImagesData()
    {
        return isset($this->_data['images_data']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasVariationAttributes()
    {
        return isset($this->_data['variation_data']['attributes']);
    }

    //########################################

    /**
     * @return string|null
     */
    public function getSku()
    {
        return $this->hasSku() ? $this->_data['sku'] : null;
    }

    /**
     * @return int|null
     */
    public function getProductId()
    {
        return $this->hasProductId() ? $this->_data['product_id'] : null;
    }

    /**
     * @return string|null
     */
    public function getProductIdType()
    {
        return $this->hasProductIdType() ? $this->_data['product_id_type'] : null;
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

        return $this->_data['type_mode']
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

        return $this->_data['type_mode'] == $listTypeNew;
    }

    // ---------------------------------------

    /**
     * @return int|null
     */
    public function getQty()
    {
        return $this->hasQty() ? $this->_data['qty'] : null;
    }

    // ---------------------------------------

    /**
     * @return string|null
     */
    public function getHandlingTime()
    {
        return $this->hasHandlingTime() ? $this->_data['handling_time'] : null;
    }

    /**
     * @return bool|null
     */
    public function getRestockDate()
    {
        return $this->hasRestockDate() ? $this->_data['restock_date'] : null;
    }

    // ---------------------------------------

    /**
     * @return float|null
     */
    public function getRegularPrice()
    {
        return $this->hasRegularPrice() ? $this->_data['price'] : null;
    }

    /**
     * @return float|null
     */
    public function getRegularSalePrice()
    {
        return $this->hasRegularSalePrice() ? $this->_data['sale_price'] : null;
    }

    /**
     * @return string|null
     */
    public function getRegularSalePriceStartDate()
    {
        return $this->hasRegularSalePrice() ? $this->_data['sale_price_start_date'] : null;
    }

    /**
     * @return string|null
     */
    public function getRegularSalePriceEndDate()
    {
        return $this->hasRegularSalePrice() ? $this->_data['sale_price_end_date'] : null;
    }

    // ---------------------------------------

    /**
     * @return float|null
     */
    public function getBusinessPrice()
    {
        return $this->hasBusinessPrice() ? $this->_data['business_price'] : null;
    }

    /**
     * @return array|null
     */
    public function getBusinessDiscounts()
    {
        return $this->hasBusinessDiscounts() ? $this->_data['business_discounts'] : null;
    }

    // ---------------------------------------

    /**
     * @return string|null
     */
    public function getCondition()
    {
        return $this->hasCondition() ? $this->_data['condition'] : null;
    }

    /**
     * @return string|null
     */
    public function getConditionNote()
    {
        return $this->hasConditionNote() ? $this->_data['condition_note'] : null;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function getGiftWrap()
    {
        return $this->hasGiftWrap() ? $this->_data['gift_wrap'] : null;
    }

    /**
     * @return bool
     */
    public function getGiftMessage()
    {
        return $this->hasGiftMessage() ? $this->_data['gift_message'] : null;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function getShippingData()
    {
        return $this->hasShippingData() ? $this->_data['shipping_data'] : null;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function getTaxCode()
    {
        return $this->hasTaxCode() ? $this->_data['tax_code'] : null;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function getNumberOfItems()
    {
        return $this->hasNumberOfItems() ? $this->_data['number_of_items'] : null;
    }

    /**
     * @return bool
     */
    public function getItemPackageQuantity()
    {
        return $this->hasItemPackageQuantity() ? $this->_data['item_package_quantity'] : null;
    }

    // ---------------------------------------

    /**
     * @return float|null
     */
    public function getBrowsenodeId()
    {
        return $this->hasBrowsenodeId() ? $this->_data['browsenode_id'] : null;
    }

    /**
     * @return string|null
     */
    public function getProductDataNick()
    {
        return $this->hasProductDataNick() ? $this->_data['product_data_nick'] : null;
    }

    // ---------------------------------------

    /**
     * @return string|null
     */
    public function getProductData()
    {
        return $this->hasProductData() ? $this->_data['product_data'] : null;
    }

    /**
     * @return string|null
     */
    public function getDescriptionData()
    {
        return $this->hasDescriptionData() ? $this->_data['description_data'] : null;
    }

    // ---------------------------------------

    /**
     * @return mixed
     */
    public function getImagesData()
    {
        return $this->hasImagesData() ? $this->_data['images_data'] : null;
    }

    // ---------------------------------------

    /**
     * @return mixed
     */
    public function getVariationAttributes()
    {
        return $this->hasVariationAttributes() ? $this->_data['variation_data']['attributes'] : null;
    }

    //########################################
}
