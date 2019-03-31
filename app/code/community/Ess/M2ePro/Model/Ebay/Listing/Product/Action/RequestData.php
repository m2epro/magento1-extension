<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_RequestData
{
    /**
     * @var array
     */
    protected $data = array();

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

    //########################################

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
    protected function getListingProduct()
    {
        return $this->listingProduct;
    }

    //########################################

    /**
     * @return bool
     */
    public function hasQty()
    {
        return !$this->isVariationItem() && isset($this->data['qty']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
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

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasPriceFixed()
    {
        return !$this->isVariationItem() && isset($this->data['price_fixed']);
    }

    /**
     * @return bool
     */
    public function hasPriceStart()
    {
        return !$this->isVariationItem() && isset($this->data['price_start']);
    }

    /**
     * @return bool
     */
    public function hasPriceReserve()
    {
        return !$this->isVariationItem() && isset($this->data['price_reserve']);
    }

    /**
     * @return bool
     */
    public function hasPriceBuyItNow()
    {
        return !$this->isVariationItem() && isset($this->data['price_buyitnow']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasOutOfStockControl()
    {
        return isset($this->data['out_of_stock_control']);
    }

    public function hasOutOfStockControlResult()
    {
        return isset($this->data['out_of_stock_control_result']);
    }

    // ---------------------------------------

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
    public function hasPrimaryCategory()
    {
        return isset($this->data['category_main_id']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasTitle()
    {
        return isset($this->data['title']);
    }

    /**
     * @return bool
     */
    public function hasSubtitle()
    {
        return isset($this->data['subtitle']);
    }

    /**
     * @return bool
     */
    public function hasDescription()
    {
        return isset($this->data['description']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasDuration()
    {
        return isset($this->data['duration']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasImages()
    {
        return isset($this->data['images']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isVariationItem()
    {
        return isset($this->data['is_variation_item']) && $this->data['is_variation_item'];
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasVariations()
    {
        return $this->isVariationItem() && isset($this->data['variation']);
    }

    /**
     * @return bool
     */
    public function hasVariationsImages()
    {
        return $this->isVariationItem() && isset($this->data['variation_image']);
    }

    //########################################

    public function getQty()
    {
        return $this->hasQty() ? $this->data['qty'] : NULL;
    }

    // ---------------------------------------

    public function getPriceFixed()
    {
        return $this->hasPriceFixed() ? $this->data['price_fixed'] : NULL;
    }

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

    // ---------------------------------------

    public function getOutOfStockControl()
    {
        return $this->hasOutOfStockControl() ? $this->data['out_of_stock_control'] : NULL;
    }

    public function getOutOfStockControlResult()
    {
        return $this->hasOutOfStockControlResult() ? $this->data['out_of_stock_control_result'] : NULL;
    }

    // ---------------------------------------

    public function getSku()
    {
        return $this->hasSku() ? $this->data['sku'] : NULL;
    }

    public function getPrimaryCategory()
    {
        return $this->hasPrimaryCategory() ? $this->data['category_main_id'] : NULL;
    }

    // ---------------------------------------

    public function getTitle()
    {
        return $this->hasTitle() ? $this->data['title'] : NULL;
    }

    public function getSubtitle()
    {
        return $this->hasSubtitle() ? $this->data['subtitle'] : NULL;
    }

    public function getDescription()
    {
        return $this->hasDescription() ? $this->data['description'] : NULL;
    }

    // ---------------------------------------

    public function getDuration()
    {
        return $this->hasDuration() ? $this->data['duration'] : NULL;
    }

    // ---------------------------------------

    public function getImages()
    {
        return $this->hasImages() ? $this->data['images'] : NULL;
    }

    // ---------------------------------------

    public function getVariations()
    {
        return $this->hasVariations() ? $this->data['variation'] : NULL;
    }

    public function getVariationsImages()
    {
        return $this->hasVariationsImages() ? $this->data['variation_image'] : NULL;
    }

    //########################################

    /**
     * @return int
     */
    public function getImagesCount()
    {
        if (!$this->hasImages()) {
            return 0;
        }

        $images = $this->getImages();
        $images = isset($images['images']) ? $images['images'] : array();

        return count($images);
    }

    /**
     * @return int
     */
    public function getTotalImagesCount()
    {
        return $this->getImagesCount() + $this->getVariationsImagesCount();
    }

    //########################################

    /**
     * @return int|null
     */
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

    /**
     * @param bool $calculateWithEmptyQty
     * @return float|null
     */
    public function getVariationPrice($calculateWithEmptyQty = true)
    {
        if (!$this->hasVariations()) {
            return NULL;
        }

        $price = NULL;

        foreach ($this->getVariations() as $variationData) {

            if ($variationData['delete'] || !isset($variationData['price'])) {
                continue;
            }

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

    /**
     * @return int
     */
    public function getVariationsImagesCount()
    {
        if (!$this->hasVariationsImages()) {
            return 0;
        }

        $images = $this->getVariationsImages();
        $images = isset($images['images']) ? $images['images'] : array();

        return count($images);
    }

    //########################################
}