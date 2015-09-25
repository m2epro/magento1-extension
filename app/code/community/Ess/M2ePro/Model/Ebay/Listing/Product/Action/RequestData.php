<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData extends Ess_M2ePro_Model_Ebay_Listing_Action_RequestData
{
    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    private $listingProduct = NULL;

    // ########################################

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $object)
    {
        $this->listingProduct = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    protected function getListingProduct()
    {
        return $this->listingProduct;
    }

    // ########################################

    public function isVariationItem()
    {
        return isset($this->data['is_variation_item']) && $this->data['is_variation_item'];
    }

    // ----------------------------------------

    public function hasVariations()
    {
        return $this->isVariationItem() && isset($this->data['variation']);
    }

    public function hasVariationsImages()
    {
        return $this->isVariationItem() && isset($this->data['variation_image']);
    }

    // ----------------------------------------

    public function hasQty()
    {
        return !$this->isVariationItem() && isset($this->data['qty']);
    }

    public function hasPrice()
    {
        return !$this->isVariationItem() &&
                (
                    $this->hasPriceFixed() ||
                    $this->hasPriceStart() ||
                    $this->hasPriceReserve() ||
                    $this->hasPriceBuyItNow()
                );
    }

    // ----------------------------------------

    public function hasPriceFixed()
    {
        return !$this->isVariationItem() && isset($this->data['price_fixed']);
    }

    public function hasPriceStart()
    {
        return !$this->isVariationItem() && isset($this->data['price_start']);
    }

    public function hasPriceReserve()
    {
        return !$this->isVariationItem() && isset($this->data['price_reserve']);
    }

    public function hasPriceBuyItNow()
    {
        return !$this->isVariationItem() && isset($this->data['price_buyitnow']);
    }

    // ----------------------------------------

    public function hasOutOfStockControl()
    {
        return isset($this->data['out_of_stock_control']);
    }

    // ----------------------------------------

    public function hasSku()
    {
        return isset($this->data['sku']);
    }

    public function hasPrimaryCategory()
    {
        return isset($this->data['category_main_id']);
    }

    // ----------------------------------------

    public function hasImages()
    {
        return isset($this->data['images']);
    }

    // ########################################

    public function getVariations()
    {
        return $this->hasVariations() ? $this->data['variation'] : NULL;
    }

    public function getVariationsImages()
    {
        return $this->hasVariationsImages() ? $this->data['variation_image'] : NULL;
    }

    // ----------------------------------------

    public function getVariationQty()
    {
        if (!$this->hasVariations()) {
            return NULL;
        }

        $qty = 0;
        foreach ($this->getVariations() as $variationData) {
            $qty += (int)$variationData['qty'];
        }

        return $qty;
    }

    public function getVariationPrice($calculateWithEmptyQty = true)
    {
        if (!$this->hasVariations()) {
            return NULL;
        }

        $price = NULL;

        foreach ($this->getVariations() as $variationData) {

            if (!$calculateWithEmptyQty && (int)$variationData['qty'] <= 0) {
                continue;
            }

            if (!is_null($price) && (float)$variationData['price'] >= $price) {
                continue;
            }

            $price = (float)$variationData['price'];
        }

        return (float)$price;
    }

    // ----------------------------------------

    public function getPriceStart()
    {
        return $this->hasPriceStart() ? $this->data['price_start'] : NULL;
    }

    public function getPriceReserve()
    {
        return $this->hasPriceReserve() ? $this->data['price_reserve'] : NULL;
    }

    public function getPriceBuyItNow()
    {
        return $this->hasPriceBuyItNow() ? $this->data['price_buyitnow'] : NULL;
    }

    // ----------------------------------------

    public function getOutOfStockControl()
    {
        return $this->hasOutOfStockControl() ? $this->data['out_of_stock_control'] : NULL;
    }

    // ----------------------------------------

    public function getSku()
    {
        return $this->hasSku() ? $this->data['sku'] : NULL;
    }

    public function getPrimaryCategory()
    {
        return $this->hasPrimaryCategory() ? $this->data['category_main_id'] : NULL;
    }

    // ----------------------------------------

    public function getImages()
    {
        return $this->hasImages() ? $this->data['images'] : NULL;
    }

    // ########################################

    public function getImagesCount()
    {
        if (!$this->hasImages()) {
            return 0;
        }

        $images = $this->getImages();
        $images = isset($images['images']) ? $images['images'] : array();

        return count($images);
    }

    public function getVariationsImagesCount()
    {
        if (!$this->hasVariationsImages()) {
            return 0;
        }

        $images = $this->getVariationsImages();
        $images = isset($images['images']) ? $images['images'] : array();

        return count($images);
    }

    public function getTotalImagesCount()
    {
        return $this->getImagesCount() + $this->getVariationsImagesCount();
    }

    // ########################################
}