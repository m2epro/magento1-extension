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
        return isset($this->_data['sku']);
    }

    /**
     * @return bool
     */
    public function hasIsNeedSkuUpdate()
    {
        return isset($this->_data['is_need_sku_update']);
    }

    /**
     * @return bool
     */
    public function hasProductIdsData()
    {
        return isset($this->_data['product_ids_data']);
    }

    public function hasIsNeedProductIdUpdate()
    {
        return isset($this->_data['is_need_product_id_update']);
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
    public function hasLagTime()
    {
        return isset($this->_data['lag_time']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasPrice()
    {
        return isset($this->_data['price']);
    }

    /**
     * @return bool
     */
    public function hasPromotionPrices()
    {
        return isset($this->_data['promotion_prices']);
    }

    // ---------------------------------------

    public function hasVariationData()
    {
        return isset($this->_data['variation_data']);
    }

    //########################################

    /**
     * @return string|null
     */
    public function getSku()
    {
        return $this->hasSku() ? $this->_data['sku'] : null;
    }

    public function getIsNeedSkuUpdate()
    {
        return $this->hasIsNeedSkuUpdate() ? $this->_data['is_need_sku_update'] : null;
    }

    /**
     * @return int|null
     */
    public function getProductIdsData()
    {
        return $this->hasProductIdsData() ? $this->_data['product_ids_data'] : null;
    }

    public function getIsNeedProductIdUpdate()
    {
        return $this->hasIsNeedProductIdUpdate() ? $this->_data['is_need_product_id_update'] : null;
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
    public function getLagTime()
    {
        return $this->hasLagTime() ? $this->_data['lag_time'] : null;
    }

    // ---------------------------------------

    /**
     * @return float|null
     */
    public function getPrice()
    {
        return $this->hasPrice() ? $this->_data['price'] : null;
    }

    // ---------------------------------------

    public function getVariationData()
    {
        return $this->hasVariationData() ? $this->_data['variation_data'] : null;
    }

    //########################################
}
