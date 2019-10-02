<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Price
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Abstract
{
    const PRICE_DISCOUNT_MAP_EXPOSURE_NONE             = 'None';
    const PRICE_DISCOUNT_MAP_EXPOSURE_DURING_CHECKOUT  = 'DuringCheckout';
    const PRICE_DISCOUNT_MAP_EXPOSURE_PRE_CHECKOUT     = 'PreCheckout';

    //########################################

    public function getData()
    {
        $data = array();

        if ($this->getEbayListingProduct()->isListingTypeFixed()) {
            $data['price_fixed'] = $this->getEbayListingProduct()->getFixedPrice();
        } else {
            $data['price_start'] = $this->getEbayListingProduct()->getStartPrice();
            $data['price_reserve'] = $this->getEbayListingProduct()->getReservePrice();
            $data['price_buyitnow'] = $this->getEbayListingProduct()->getBuyItNowPrice();
        }

        $data = array_merge(
            $data,
            $this->getPriceDiscountStpData(),
            $this->getPriceDiscountMapData()
        );

        return $data;
    }

    //########################################

    /**
     * @return array
     */
    protected function getPriceDiscountStpData()
    {
        if (!$this->getEbayListingProduct()->isListingTypeFixed() ||
            !$this->getEbayListingProduct()->isPriceDiscountStp()) {
            return array();
        }

        $data = array(
            'original_retail_price' => $this->getEbayListingProduct()->getPriceDiscountStp()
        );

        if ($this->getEbayMarketplace()->isStpAdvancedEnabled()) {
            $data = array_merge(
                $data,
                $this->getEbayListingProduct()->getEbaySellingFormatTemplate()->getPriceDiscountStpAdditionalFlags()
            );
        }

        return array('price_discount_stp' => $data);
    }

    /**
     * @return array
     */
    protected function getPriceDiscountMapData()
    {
        if (!$this->getEbayListingProduct()->isListingTypeFixed() ||
            !$this->getEbayListingProduct()->isPriceDiscountMap()) {
            return array();
        }

        $data = array(
            'minimum_advertised_price' => $this->getEbayListingProduct()->getPriceDiscountMap(),
        );

        $exposure = $this->getEbayListingProduct()->getEbaySellingFormatTemplate()->getPriceDiscountMapExposureType();
        $data['minimum_advertised_price_exposure'] = self::getPriceDiscountMapExposureType($exposure);

        return array('price_discount_map' => $data);
    }

    public static function getPriceDiscountMapExposureType($type)
    {
        switch ($type) {
            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_DISCOUNT_MAP_EXPOSURE_NONE:
                return self::PRICE_DISCOUNT_MAP_EXPOSURE_NONE;

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_DISCOUNT_MAP_EXPOSURE_DURING_CHECKOUT:
                return self::PRICE_DISCOUNT_MAP_EXPOSURE_DURING_CHECKOUT;

            case Ess_M2ePro_Model_Ebay_Template_SellingFormat::PRICE_DISCOUNT_MAP_EXPOSURE_PRE_CHECKOUT:
                return self::PRICE_DISCOUNT_MAP_EXPOSURE_PRE_CHECKOUT;

            default:
                return self::PRICE_DISCOUNT_MAP_EXPOSURE_NONE;
        }
    }

    //########################################
}
