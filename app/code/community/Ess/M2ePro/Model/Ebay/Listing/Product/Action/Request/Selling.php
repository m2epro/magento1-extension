<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Selling
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Abstract
{
    const LISTING_TYPE_AUCTION  = 'Chinese';
    const LISTING_TYPE_FIXED    = 'FixedPriceItem';

    const PRICE_DISCOUNT_MAP_EXPOSURE_NONE             = 'None';
    const PRICE_DISCOUNT_MAP_EXPOSURE_DURING_CHECKOUT  = 'DuringCheckout';
    const PRICE_DISCOUNT_MAP_EXPOSURE_PRE_CHECKOUT     = 'PreCheckout';

    /**
     * @var Ess_M2ePro_Model_Template_SellingFormat
     */
    private $sellingFormatTemplate = NULL;

    //########################################

    /**
     * @return array
     */
    public function getData()
    {
        $data = array();

        if ($this->getConfigurator()->isGeneralAllowed()) {

            $data = array_merge(
                $this->getGeneralData(),
                $this->getVatTaxData(),
                $this->getRestrictedToBusinessData(),
                $this->getCharityData()
            );
        }

        return array_merge(
            $data,
            $this->getQtyData(),
            $this->getPriceData(),
            $this->getPriceDiscountStpData(),
            $this->getPriceDiscountMapData()
        );
    }

    //########################################

    /**
     * @return array
     */
    public function getGeneralData()
    {
        $data = array(
            'duration' => $this->getSellingFormatSource()->getDuration(),
            'is_private' => $this->getEbaySellingFormatTemplate()->isPrivateListing(),
            'currency' => $this->getEbayMarketplace()->getCurrency(),
            'out_of_stock_control' => $this->getEbaySellingFormatTemplate()->getOutOfStockControl()
        );

        if ($this->getEbayListingProduct()->isListingTypeFixed()) {
            $data['listing_type'] = self::LISTING_TYPE_FIXED;
        } else {
            $data['listing_type'] = self::LISTING_TYPE_AUCTION;
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getVatTaxData()
    {
        $data = array(
            'tax_category' => $this->getSellingFormatSource()->getTaxCategory()
        );

        if ($this->getEbayMarketplace()->isVatEnabled()) {
            $data['vat_percent'] = $this->getEbaySellingFormatTemplate()->getVatPercent();
        }

        if ($this->getEbayMarketplace()->isTaxTableEnabled()) {
            $data['use_tax_table'] = $this->getEbaySellingFormatTemplate()->isTaxTableEnabled();
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getRestrictedToBusinessData()
    {
        $data = array();

        if ($this->getEbaySellingFormatTemplate()->isRestrictedToBusinessEnabled()) {
            $data['restricted_to_business'] = $this->getEbaySellingFormatTemplate()
                                                   ->isRestrictedToBusinessEnabled();
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getCharityData()
    {
        $charity = $this->getEbaySellingFormatTemplate()->getCharity();

        if (is_null($charity)) {
            return array();
        }

        return array(
            'charity_id' => $charity['id'],
            'charity_percent' => $charity['percentage']
        );
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getQtyData()
    {
        if (!$this->getConfigurator()->isQtyAllowed() ||
            $this->getIsVariationItem()) {
            return array();
        }

        $data = array(
            'qty' => $this->getEbayListingProduct()->getQty()
        );

        $this->checkQtyWarnings();

        return $data;
    }

    /**
     * @return array
     */
    public function getPriceData()
    {
        if (!$this->getConfigurator()->isPriceAllowed() ||
            $this->getIsVariationItem()) {
            return array();
        }

        $data = array();

        if ($this->getEbayListingProduct()->isListingTypeFixed()) {

            $data['price_fixed'] = $this->getEbayListingProduct()->getFixedPrice();

            $data['bestoffer_mode'] = $this->getEbaySellingFormatTemplate()->isBestOfferEnabled();

            if ($data['bestoffer_mode']) {
                $data['bestoffer_accept_price'] = $this->getEbayListingProduct()->getBestOfferAcceptPrice();
                $data['bestoffer_reject_price'] = $this->getEbayListingProduct()->getBestOfferRejectPrice();
            }

        } else {
            $data['price_start'] = $this->getEbayListingProduct()->getStartPrice();
            $data['price_reserve'] = $this->getEbayListingProduct()->getReservePrice();
            $data['price_buyitnow'] = $this->getEbayListingProduct()->getBuyItNowPrice();
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getPriceDiscountStpData()
    {
        if (!$this->getConfigurator()->isPriceAllowed() ||
            $this->getIsVariationItem()) {
            return array();
        }

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
                $this->getEbaySellingFormatTemplate()->getPriceDiscountStpAdditionalFlags()
            );
        }

        return array('price_discount_stp' => $data);
    }

    /**
     * @return array
     */
    public function getPriceDiscountMapData()
    {
        if (!$this->getConfigurator()->isPriceAllowed() ||
            $this->getIsVariationItem()) {
            return array();
        }

        if (!$this->getEbayListingProduct()->isListingTypeFixed() ||
            !$this->getEbayListingProduct()->isPriceDiscountMap()) {
            return array();
        }

        $data = array(
            'minimum_advertised_price' => $this->getEbayListingProduct()->getPriceDiscountMap(),
        );

        $exposure = $this->getEbaySellingFormatTemplate()->getPriceDiscountMapExposureType();
        $data['minimum_advertised_price_exposure'] =
            Ess_M2ePro_Model_Ebay_Listing_Product_Action_Request_Selling::
                getPriceDiscountMapExposureType($exposure);

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

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    private function getSellingFormatTemplate()
    {
        if (is_null($this->sellingFormatTemplate)) {
            $this->sellingFormatTemplate = $this->getListingProduct()
                                                ->getChildObject()
                                                ->getSellingFormatTemplate();
        }
        return $this->sellingFormatTemplate;
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_SellingFormat
     */
    private function getEbaySellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_SellingFormat_Source
     */
    private function getSellingFormatSource()
    {
        return $this->getEbayListingProduct()->getSellingFormatTemplateSource();
    }

    //########################################

    public function checkQtyWarnings()
    {
        $qtyMode = $this->getEbaySellingFormatTemplate()->getQtyMode();
        if ($qtyMode == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED ||
            $qtyMode == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT) {

            $listingProductId = $this->getListingProduct()->getId();
            $productId = $this->getListingProduct()->getProductId();
            $storeId = $this->getListingProduct()->getListing()->getStoreId();

            if (!empty(Ess_M2ePro_Model_Magento_Product::$statistics[$listingProductId][$productId][$storeId]['qty'])) {

                $qtys = Ess_M2ePro_Model_Magento_Product::$statistics[$listingProductId][$productId][$storeId]['qty'];
                foreach ($qtys as $type => $override) {
                    $this->addQtyWarnings($type);
                }
            }
        }
    }

    /**
     * @param int $type
     */
    public function addQtyWarnings($type)
    {
        if ($type === Ess_M2ePro_Model_Magento_Product::FORCING_QTY_TYPE_MANAGE_STOCK_NO) {
        // M2ePro_TRANSLATIONS
        // During the Quantity Calculation the Settings in the "Manage Stock No" field were taken into consideration.
            $this->addWarningMessage('During the Quantity Calculation the Settings in the "Manage Stock No" '.
                                     'field were taken into consideration.');
        }

        if ($type === Ess_M2ePro_Model_Magento_Product::FORCING_QTY_TYPE_BACKORDERS) {
            // M2ePro_TRANSLATIONS
            // During the Quantity Calculation the Settings in the "Backorders" field were taken into consideration.
            $this->addWarningMessage('During the Quantity Calculation the Settings in the "Backorders" '.
                                     'field were taken into consideration.');
        }
    }

    //########################################
}