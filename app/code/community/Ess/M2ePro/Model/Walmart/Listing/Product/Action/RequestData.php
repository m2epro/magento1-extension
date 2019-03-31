<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_RequestData
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
    public function hasIsNeedSkuUpdate()
    {
        return isset($this->data['is_need_sku_update']);
    }

    /**
     * @return bool
     */
    public function hasProductIdsData()
    {
        return isset($this->data['product_ids_data']);
    }

    public function hasIsNeedProductIdUpdate()
    {
        return isset($this->data['is_need_product_id_update']);
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
    public function hasLagTime()
    {
        return isset($this->data['lag_time']);
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
    public function hasPromotionPrices()
    {
        return isset($this->data['promotion_prices']);
    }

    // ---------------------------------------

    public function hasVariationData()
    {
        return isset($this->data['variation_data']);
    }

    //########################################

    /**
     * @return string|null
     */
    public function getSku()
    {
        return $this->hasSku() ? $this->data['sku'] : NULL;
    }

    public function getIsNeedSkuUpdate()
    {
        return $this->hasIsNeedSkuUpdate() ? $this->data['is_need_sku_update'] : NULL;
    }

    /**
     * @return int|null
     */
    public function getProductIdsData()
    {
        return $this->hasProductIdsData() ? $this->data['product_ids_data'] : NULL;
    }

    public function getIsNeedProductIdUpdate()
    {
        return $this->hasIsNeedProductIdUpdate() ? $this->data['is_need_product_id_update'] : NULL;
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
    public function getLagTime()
    {
        return $this->hasLagTime() ? $this->data['lag_time'] : NULL;
    }

    // ---------------------------------------

    /**
     * @return float|null
     */
    public function getPrice()
    {
        return $this->hasPrice() ? $this->data['price'] : NULL;
    }

    // ---------------------------------------

    public function getVariationData()
    {
        return $this->hasVariationData() ? $this->data['variation_data'] : NULL;
    }

    //########################################
}