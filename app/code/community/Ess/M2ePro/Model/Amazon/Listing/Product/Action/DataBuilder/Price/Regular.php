<?php

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Price_Regular
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_DataBuilder_Abstract
{
    /**
     * @return array
     */
    public function getData()
    {
        $data = array();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $this->getAmazonListingProduct();
        if (!isset($this->_cachedData['regular_price'])) {
            $this->_cachedData['regular_price'] = $amazonListingProduct->getRegularPrice();
        }

        if (!isset($this->_cachedData['regular_map_price'])) {
            $this->_cachedData['regular_map_price'] = $amazonListingProduct->getRegularMapPrice();
        }

        if (!isset($this->_cachedData['regular_sale_price_info'])) {
            $salePriceInfo                                = $amazonListingProduct->getRegularSalePriceInfo();
            $this->_cachedData['regular_sale_price_info'] = $salePriceInfo;
        }

        $data['price'] = $this->_cachedData['regular_price'];

        if ((float)$this->_cachedData['regular_map_price'] <= 0) {
            $data['map_price'] = 0;
        } else {
            $data['map_price'] = $this->_cachedData['regular_map_price'];
        }

        if ($this->_cachedData['regular_sale_price_info'] === false) {
            $data['sale_price'] = 0;
        } else {
            $data['sale_price']            = $this->_cachedData['regular_sale_price_info']['price'];
            $data['sale_price_start_date'] = $this->_cachedData['regular_sale_price_info']['start_date'];
            $data['sale_price_end_date']   = $this->_cachedData['regular_sale_price_info']['end_date'];
        }

        if (
            !$amazonListingProduct->isGeneralIdOwner()
            && !$amazonListingProduct->getVariationManager()
                                     ->isVariationParent()
        ) {
            $listPrice = $amazonListingProduct->getRegularListPrice();
            if ($listPrice > 0) {
                $data['list_price'] = $listPrice;
            }
        }

        return $data;
    }
}
