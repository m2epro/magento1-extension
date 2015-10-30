<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
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
    public function hasPrice()
    {
        return isset($this->data['price']);
    }

    /**
     * @return bool
     */
    public function hasSalePrice()
    {
        return isset($this->data['sale_price']);
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
        return $this->hasRestockDate() ? isset($this->data['restock_date']) : NULL;
    }

    // ---------------------------------------

    /**
     * @return float|null
     */
    public function getPrice()
    {
        return $this->hasPrice() ? $this->data['price'] : NULL;
    }

    /**
     * @return float|null
     */
    public function getSalePrice()
    {
        return $this->hasSalePrice() ? $this->data['sale_price'] : NULL;
    }

    /**
     * @return string|null
     */
    public function getSalePriceStartDate()
    {
        return $this->hasSalePrice() ? $this->data['sale_price_start_date'] : NULL;
    }

    /**
     * @return string|null
     */
    public function getSalePriceEndDate()
    {
        return $this->hasSalePrice() ? $this->data['sale_price_end_date'] : NULL;
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