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
    protected $_data = array();

    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    protected $_listingProduct = null;

    //########################################

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->_data = $data;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $object
     */
    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $object)
    {
        $this->_listingProduct = $object;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    protected function getListingProduct()
    {
        return $this->_listingProduct;
    }

    //########################################

    /**
     * @return bool
     */
    public function hasQty()
    {
        return !$this->isVariationItem() && isset($this->_data['qty']);
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
        return !$this->isVariationItem() && isset($this->_data['price_fixed']);
    }

    /**
     * @return bool
     */
    public function hasPriceStart()
    {
        return !$this->isVariationItem() && isset($this->_data['price_start']);
    }

    /**
     * @return bool
     */
    public function hasPriceReserve()
    {
        return !$this->isVariationItem() && isset($this->_data['price_reserve']);
    }

    /**
     * @return bool
     */
    public function hasPriceBuyItNow()
    {
        return !$this->isVariationItem() && isset($this->_data['price_buyitnow']);
    }

    // ---------------------------------------

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
    public function hasPrimaryCategory()
    {
        return isset($this->_data['category_main_id']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasTitle()
    {
        return isset($this->_data['title']);
    }

    /**
     * @return bool
     */
    public function hasSubtitle()
    {
        return isset($this->_data['subtitle']);
    }

    /**
     * @return bool
     */
    public function hasDescription()
    {
        return isset($this->_data['description']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasDuration()
    {
        return isset($this->_data['duration']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasImages()
    {
        return isset($this->_data['images']);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isVariationItem()
    {
        return isset($this->_data['is_variation_item']) && $this->_data['is_variation_item'];
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function hasVariations()
    {
        return $this->isVariationItem() && isset($this->_data['variation']);
    }

    /**
     * @return bool
     */
    public function hasVariationsImages()
    {
        return $this->isVariationItem() && isset($this->_data['variation_image']);
    }

    //########################################

    public function getQty()
    {
        return $this->hasQty() ? $this->_data['qty'] : null;
    }

    // ---------------------------------------

    public function getPriceFixed()
    {
        return $this->hasPriceFixed() ? $this->_data['price_fixed'] : null;
    }

    public function getPriceStart()
    {
        return $this->hasPriceStart() ? $this->_data['price_start'] : null;
    }

    public function getPriceReserve()
    {
        return $this->hasPriceReserve() ? $this->_data['price_reserve'] : null;
    }

    public function getPriceBuyItNow()
    {
        return $this->hasPriceBuyItNow() ? $this->_data['price_buyitnow'] : null;
    }

    // ---------------------------------------

    public function getSku()
    {
        return $this->hasSku() ? $this->_data['sku'] : null;
    }

    public function getPrimaryCategory()
    {
        return $this->hasPrimaryCategory() ? $this->_data['category_main_id'] : null;
    }

    // ---------------------------------------

    public function getTitle()
    {
        return $this->hasTitle() ? $this->_data['title'] : null;
    }

    public function getSubtitle()
    {
        return $this->hasSubtitle() ? $this->_data['subtitle'] : null;
    }

    public function getDescription()
    {
        return $this->hasDescription() ? $this->_data['description'] : null;
    }

    // ---------------------------------------

    public function getDuration()
    {
        return $this->hasDuration() ? $this->_data['duration'] : null;
    }

    // ---------------------------------------

    public function getImages()
    {
        return $this->hasImages() ? $this->_data['images'] : null;
    }

    // ---------------------------------------

    public function getVariations()
    {
        return $this->hasVariations() ? $this->_data['variation'] : null;
    }

    public function getVariationsImages()
    {
        return $this->hasVariationsImages() ? $this->_data['variation_image'] : null;
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
            return null;
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
            return null;
        }

        $price = null;

        foreach ($this->getVariations() as $variationData) {
            if ($variationData['delete'] || !isset($variationData['price'])) {
                continue;
            }

            if (!$calculateWithEmptyQty && (int)$variationData['qty'] <= 0) {
                continue;
            }

            if ($price !== null && (float)$variationData['price'] >= $price) {
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
