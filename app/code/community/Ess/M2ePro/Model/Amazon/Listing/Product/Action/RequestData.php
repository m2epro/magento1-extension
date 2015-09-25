<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_RequestData
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

    public function hasTypeMode()
    {
        return isset($this->data['type_mode']);
    }

    // ----------------------------------------

    public function hasQty()
    {
        return isset($this->data['qty']);
    }

    // ----------------------------------------

    public function hasHandlingTime()
    {
        return isset($this->data['handling_time']);
    }

    public function hasRestockDate()
    {
        return isset($this->data['restock_date']);
    }

    // ----------------------------------------

    public function hasPrice()
    {
        return isset($this->data['price']);
    }

    public function hasSalePrice()
    {
        return isset($this->data['sale_price']);
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

    public function hasBrowsenodeId()
    {
        return isset($this->data['browsenode_id']);
    }

    public function hasProductDataNick()
    {
        return isset($this->data['product_data_nick']);
    }

    // ----------------------------------------

    public function hasProductData()
    {
        return isset($this->data['product_data']);
    }

    public function hasDescriptionData()
    {
        return isset($this->data['description_data']);
    }

    // ----------------------------------------

    public function hasImagesData()
    {
        return isset($this->data['images_data']);
    }

    // ----------------------------------------

    public function hasVariationAttributes()
    {
        return isset($this->data['variation_data']['attributes']);
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

    public function isTypeModeExist()
    {
        if (!$this->hasTypeMode()) {
            return false;
        }

        return $this->data['type_mode']
            == Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Request::LIST_TYPE_EXIST;
    }

    public function isTypeModeNew()
    {
        if (!$this->hasTypeMode()) {
            return false;
        }

        $listTypeNew = Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Request::LIST_TYPE_NEW;

        return $this->data['type_mode'] == $listTypeNew;
    }

    // ----------------------------------------

    public function getQty()
    {
        return $this->hasQty() ? $this->data['qty'] : NULL;
    }

    // ----------------------------------------

    public function getHandlingTime()
    {
        return $this->hasHandlingTime() ? $this->data['handling_time'] : NULL;
    }

    public function getRestockDate()
    {
        return $this->hasRestockDate() ? isset($this->data['restock_date']) : NULL;
    }

    // ----------------------------------------

    public function getPrice()
    {
        return $this->hasPrice() ? $this->data['price'] : NULL;
    }

    public function getSalePrice()
    {
        return $this->hasSalePrice() ? $this->data['sale_price'] : NULL;
    }

    public function getSalePriceStartDate()
    {
        return $this->hasSalePrice() ? $this->data['sale_price_start_date'] : NULL;
    }

    public function getSalePriceEndDate()
    {
        return $this->hasSalePrice() ? $this->data['sale_price_end_date'] : NULL;
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

    public function getBrowsenodeId()
    {
        return $this->hasBrowsenodeId() ? $this->data['browsenode_id'] : NULL;
    }

    public function getProductDataNick()
    {
        return $this->hasProductDataNick() ? $this->data['product_data_nick'] : NULL;
    }

    // ----------------------------------------

    public function getProductData()
    {
        return $this->hasProductData() ? $this->data['product_data'] : NULL;
    }

    public function getDescriptionData()
    {
        return $this->hasDescriptionData() ? $this->data['description_data'] : NULL;
    }

    // ----------------------------------------

    public function getImagesData()
    {
        return $this->hasImagesData() ? $this->data['images_data'] : NULL;
    }

    // ----------------------------------------

    public function getVariationAttributes()
    {
        return $this->hasVariationAttributes() ? $this->data['variation_data']['attributes'] : NULL;
    }

    // ########################################
}