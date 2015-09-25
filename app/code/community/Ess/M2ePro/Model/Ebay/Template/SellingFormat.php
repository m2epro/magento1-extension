<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

/**
 * @method Ess_M2ePro_Model_Template_SellingFormat getParentObject()
 * @method Ess_M2ePro_Model_Mysql4_Ebay_Template_SellingFormat getResource()
 */
class Ess_M2ePro_Model_Ebay_Template_SellingFormat extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    const LISTING_TYPE_AUCTION      = 1;
    const LISTING_TYPE_FIXED        = 2;
    const LISTING_TYPE_ATTRIBUTE    = 3;

    const LISTING_IS_PRIVATE_NO   = 0;
    const LISTING_IS_PRIVATE_YES  = 1;

    const DURATION_TYPE_EBAY       = 1;
    const DURATION_TYPE_ATTRIBUTE  = 2;

    const QTY_MODIFICATION_MODE_OFF = 0;
    const QTY_MODIFICATION_MODE_ON = 1;

    const QTY_MIN_POSTED_DEFAULT_VALUE = 1;
    const QTY_MAX_POSTED_DEFAULT_VALUE = 100;

    const TAX_CATEGORY_MODE_NONE      = 0;
    const TAX_CATEGORY_MODE_VALUE     = 1;
    const TAX_CATEGORY_MODE_ATTRIBUTE = 2;

    const PRICE_COEFFICIENT_NONE                = 0;
    const PRICE_COEFFICIENT_ABSOLUTE_INCREASE   = 1;
    const PRICE_COEFFICIENT_ABSOLUTE_DECREASE   = 2;
    const PRICE_COEFFICIENT_PERCENTAGE_INCREASE = 3;
    const PRICE_COEFFICIENT_PERCENTAGE_DECREASE = 4;

    const PRICE_VARIATION_MODE_PARENT        = 1;
    const PRICE_VARIATION_MODE_CHILDREN      = 2;

    const PRICE_DISCOUNT_STP_TYPE_RRP           = 0;
    const PRICE_DISCOUNT_STP_TYPE_SOLD_ON_EBAY  = 1;
    const PRICE_DISCOUNT_STP_TYPE_SOLD_OFF_EBAY = 2;
    const PRICE_DISCOUNT_STP_TYPE_SOLD_ON_BOTH  = 3;

    const PRICE_DISCOUNT_MAP_EXPOSURE_NONE             = 0;
    const PRICE_DISCOUNT_MAP_EXPOSURE_DURING_CHECKOUT  = 1;
    const PRICE_DISCOUNT_MAP_EXPOSURE_PRE_CHECKOUT     = 2;

    const BEST_OFFER_MODE_NO  = 0;
    const BEST_OFFER_MODE_YES = 1;

    const BEST_OFFER_ACCEPT_MODE_NO          = 0;
    const BEST_OFFER_ACCEPT_MODE_PERCENTAGE  = 1;
    const BEST_OFFER_ACCEPT_MODE_ATTRIBUTE   = 2;

    const BEST_OFFER_REJECT_MODE_NO          = 0;
    const BEST_OFFER_REJECT_MODE_PERCENTAGE  = 1;
    const BEST_OFFER_REJECT_MODE_ATTRIBUTE   = 2;

    const RESTRICTED_TO_BUSINESS_DISABLED = 0;
    const RESTRICTED_TO_BUSINESS_ENABLED  = 1;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_SellingFormat_Source[]
     */
    private $sellingSourceModels = array();

    // ########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_SellingFormat');
    }

    // ########################################

    public function getNick()
    {
        return Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT;
    }

    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::getModel('M2ePro/Ebay_Listing')
                            ->getCollection()
                            ->addFieldToFilter('template_selling_format_mode',
                                                Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE)
                            ->addFieldToFilter('template_selling_format_id', $this->getId())
                            ->getSize() ||
               (bool)Mage::getModel('M2ePro/Ebay_Listing_Product')
                            ->getCollection()
                            ->addFieldToFilter('template_selling_format_mode',
                                                Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE)
                            ->addFieldToFilter('template_selling_format_id', $this->getId())
                            ->getSize();
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->sellingSourceModels = array();

        $this->delete();
        return true;
    }

    // ########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Ebay_Template_SellingFormat_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->sellingSourceModels[$productId])) {
            return $this->sellingSourceModels[$productId];
        }

        $this->sellingSourceModels[$productId] = Mage::getModel('M2ePro/Ebay_Template_SellingFormat_Source');
        $this->sellingSourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->sellingSourceModels[$productId]->setSellingFormatTemplate($this->getParentObject());

        return $this->sellingSourceModels[$productId];
    }

    // ########################################

    public function getListingType()
    {
        return (int)$this->getData('listing_type');
    }

    public function isListingTypeFixed()
    {
        return $this->getListingType() == self::LISTING_TYPE_FIXED;
    }

    public function isListingTypeAuction()
    {
        return $this->getListingType() == self::LISTING_TYPE_AUCTION;
    }

    public function isListingTypeAttribute()
    {
        return $this->getListingType() == self::LISTING_TYPE_ATTRIBUTE;
    }

    public function getListingTypeSource()
    {
        return array(
            'mode'      => $this->getListingType(),
            'attribute' => $this->getData('listing_type_attribute')
        );
    }

    public function getListingTypeAttributes()
    {
        $attributes = array();
        $src = $this->getListingTypeSource();

        if ($src['mode'] == self::LISTING_TYPE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getDurationMode()
    {
        return (int)$this->getData('duration_mode');
    }

    public function getDurationSource()
    {
        $tempSrc = $this->getListingTypeSource();

        $mode = self::DURATION_TYPE_EBAY;
        if ($tempSrc['mode'] == self::LISTING_TYPE_ATTRIBUTE) {
            $mode = self::DURATION_TYPE_ATTRIBUTE;
        }

        return array(
            'mode'     => (int)$mode,
            'value'     => (int)$this->getDurationMode(),
            'attribute' => $this->getData('duration_attribute')
        );
    }

    public function getDurationAttributes()
    {
        $attributes = array();
        $src = $this->getDurationSource();

        if ($src['mode'] == self::DURATION_TYPE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getOutOfStockControl()
    {
        return (bool)$this->getData('out_of_stock_control');
    }

    //-------------------------

    public function isPrivateListing()
    {
        return (bool)$this->getData('listing_is_private');
    }

    //-------------------------

    public function getQtyMode()
    {
        return (int)$this->getData('qty_mode');
    }

    public function isQtyModeProduct()
    {
        return $this->getQtyMode() == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT;
    }

    public function isQtyModeSingle()
    {
        return $this->getQtyMode() == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_SINGLE;
    }

    public function isQtyModeNumber()
    {
        return $this->getQtyMode() == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_NUMBER;
    }

    public function isQtyModeAttribute()
    {
        return $this->getQtyMode() == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_ATTRIBUTE;
    }

    public function isQtyModeProductFixed()
    {
        return $this->getQtyMode() == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED;
    }

    public function getQtyNumber()
    {
        return (int)$this->getData('qty_custom_value');
    }

    public function getQtySource()
    {
        return array(
            'mode'      => $this->getQtyMode(),
            'value'     => $this->getQtyNumber(),
            'attribute' => $this->getData('qty_custom_attribute'),
            'qty_modification_mode' => $this->getQtyModificationMode(),
            'qty_min_posted_value'      => $this->getQtyMinPostedValue(),
            'qty_max_posted_value'      => $this->getQtyMaxPostedValue(),
            'qty_percentage'            => $this->getQtyPercentage()
        );
    }

    public function getQtyAttributes()
    {
        $attributes = array();
        $src = $this->getQtySource();

        if ($src['mode'] == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getQtyPercentage()
    {
        return (int)$this->getData('qty_percentage');
    }

    //-------------------------

    public function getQtyModificationMode()
    {
        return (int)$this->getData('qty_modification_mode');
    }

    public function isQtyModificationModeOn()
    {
        return $this->getQtyModificationMode() == self::QTY_MODIFICATION_MODE_ON;
    }

    public function isQtyModificationModeOff()
    {
        return $this->getQtyModificationMode() == self::QTY_MODIFICATION_MODE_OFF;
    }

    public function getQtyMinPostedValue()
    {
        return (int)$this->getData('qty_min_posted_value');
    }

    public function getQtyMaxPostedValue()
    {
        return (int)$this->getData('qty_max_posted_value');
    }

    //-------------------------

    public function getVatPercent()
    {
        return (float)$this->getData('vat_percent');
    }

    public function isTaxTableEnabled()
    {
        return (bool)$this->getData('tax_table_mode');
    }

    public function getTaxCategorySource()
    {
        return array(
            'mode'      => $this->getData('tax_category_mode'),
            'value'     => $this->getData('tax_category_value'),
            'attribute' => $this->getData('tax_category_attribute')
        );
    }

    public function isRestrictedToBusinessEnabled()
    {
        return (bool)$this->getData('restricted_to_business');
    }

    //-------------------------

    public function isPriceIncreaseVatPercentEnabled()
    {
        return (bool)$this->getData('price_increase_vat_percent');
    }

    //-------------------------

    public function getPriceVariationMode()
    {
        return (int)$this->getData('price_variation_mode');
    }

    public function isPriceVariationModeParent()
    {
        return $this->getPriceVariationMode() == self::PRICE_VARIATION_MODE_PARENT;
    }

    public function isPriceVariationModeChildren()
    {
        return $this->getPriceVariationMode() == self::PRICE_VARIATION_MODE_CHILDREN;
    }

    //-------------------------

    public function getFixedPriceMode()
    {
        return (int)$this->getData('fixed_price_mode');
    }

    public function isFixedPriceModeNone()
    {
        return $this->getFixedPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_NONE;
    }

    public function isFixedPriceModeProduct()
    {
        return $this->getFixedPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_PRODUCT;
    }

    public function isFixedPriceModeSpecial()
    {
        return $this->getFixedPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_SPECIAL;
    }

    public function isFixedPriceModeAttribute()
    {
        return $this->getFixedPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_ATTRIBUTE;
    }

    public function getFixedPriceCoefficient()
    {
        return $this->getData('fixed_price_coefficient');
    }

    public function getFixedPriceSource()
    {
        return array(
            'mode'        => $this->getFixedPriceMode(),
            'coefficient' => $this->getFixedPriceCoefficient(),
            'attribute'   => $this->getData('fixed_price_custom_attribute')
        );
    }

    public function getFixedPriceAttributes()
    {
        $attributes = array();
        $src = $this->getFixedPriceSource();

        if ($src['mode'] == Ess_M2ePro_Model_Template_SellingFormat::PRICE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getStartPriceMode()
    {
        return (int)$this->getData('start_price_mode');
    }

    public function isStartPriceModeNone()
    {
        return $this->getStartPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_NONE;
    }

    public function isStartPriceModeProduct()
    {
        return $this->getStartPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_PRODUCT;
    }

    public function isStartPriceModeSpecial()
    {
        return $this->getStartPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_SPECIAL;
    }

    public function isStartPriceModeAttribute()
    {
        return $this->getStartPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_ATTRIBUTE;
    }

    public function getStartPriceCoefficient()
    {
        return $this->getData('start_price_coefficient');
    }

    public function getStartPriceSource()
    {
        return array(
            'mode'        => $this->getStartPriceMode(),
            'coefficient' => $this->getStartPriceCoefficient(),
            'attribute'   => $this->getData('start_price_custom_attribute')
        );
    }

    public function getStartPriceAttributes()
    {
        $attributes = array();
        $src = $this->getStartPriceSource();

        if ($src['mode'] == Ess_M2ePro_Model_Template_SellingFormat::PRICE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getReservePriceMode()
    {
        return (int)$this->getData('reserve_price_mode');
    }

    public function isReservePriceModeNone()
    {
        return $this->getReservePriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_NONE;
    }

    public function isReservePriceModeProduct()
    {
        return $this->getReservePriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_PRODUCT;
    }

    public function isReservePriceModeSpecial()
    {
        return $this->getReservePriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_SPECIAL;
    }

    public function isReservePriceModeAttribute()
    {
        return $this->getReservePriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_ATTRIBUTE;
    }

    public function getReservePriceCoefficient()
    {
        return $this->getData('reserve_price_coefficient');
    }

    public function getReservePriceSource()
    {
        return array(
            'mode'        => $this->getReservePriceMode(),
            'coefficient' => $this->getReservePriceCoefficient(),
            'attribute'   => $this->getData('reserve_price_custom_attribute')
        );
    }

    public function getReservePriceAttributes()
    {
        $attributes = array();
        $src = $this->getReservePriceSource();

        if ($src['mode'] == Ess_M2ePro_Model_Template_SellingFormat::PRICE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getBuyItNowPriceMode()
    {
        return (int)$this->getData('buyitnow_price_mode');
    }

    public function isBuyItNowPriceModeNone()
    {
        return $this->getBuyItNowPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_NONE;
    }

    public function isBuyItNowPriceModeProduct()
    {
        return $this->getBuyItNowPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_PRODUCT;
    }

    public function isBuyItNowPriceModeSpecial()
    {
        return $this->getBuyItNowPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_SPECIAL;
    }

    public function isBuyItNowPriceModeAttribute()
    {
        return $this->getBuyItNowPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_ATTRIBUTE;
    }

    public function getBuyItNowPriceCoefficient()
    {
        return $this->getData('buyitnow_price_coefficient');
    }

    public function getBuyItNowPriceSource()
    {
        return array(
            'mode'      => $this->getBuyItNowPriceMode(),
            'coefficient' => $this->getBuyItNowPriceCoefficient(),
            'attribute' => $this->getData('buyitnow_price_custom_attribute')
        );
    }

    public function getBuyItNowPriceAttributes()
    {
        $attributes = array();
        $src = $this->getBuyItNowPriceSource();

        if ($src['mode'] == Ess_M2ePro_Model_Template_SellingFormat::PRICE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getPriceDiscountStpMode()
    {
        return (int)$this->getData('price_discount_stp_mode');
    }

    public function isPriceDiscountStpModeNone()
    {
        return $this->getPriceDiscountStpMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_NONE;
    }

    public function isPriceDiscountStpModeProduct()
    {
        return $this->getPriceDiscountStpMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_PRODUCT;
    }

    public function isPriceDiscountStpModeSpecial()
    {
        return $this->getPriceDiscountStpMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_SPECIAL;
    }

    public function isPriceDiscountStpModeAttribute()
    {
        return $this->getPriceDiscountStpMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_ATTRIBUTE;
    }

    public function getPriceDiscountStpSource()
    {
        return array(
            'mode'      => $this->getPriceDiscountStpMode(),
            'attribute' => $this->getData('price_discount_stp_attribute')
        );
    }

    public function getPriceDiscountStpAttributes()
    {
        $attributes = array();
        $src = $this->getPriceDiscountStpSource();

        if ($src['mode'] == Ess_M2ePro_Model_Template_SellingFormat::PRICE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getPriceDiscountStpType()
    {
        return (int)$this->getData('price_discount_stp_type');
    }

    public function isPriceDiscountStpTypeRrp()
    {
        return $this->getPriceDiscountStpType() == self::PRICE_DISCOUNT_STP_TYPE_RRP;
    }

    public function isPriceDiscountStpTypeSoldOnEbay()
    {
        return $this->getPriceDiscountStpType() == self::PRICE_DISCOUNT_STP_TYPE_SOLD_ON_EBAY;
    }

    public function isPriceDiscountStpTypeSoldOffEbay()
    {
        return $this->getPriceDiscountStpType() == self::PRICE_DISCOUNT_STP_TYPE_SOLD_OFF_EBAY;
    }

    public function isPriceDiscountStpTypeSoldOnBoth()
    {
        return $this->getPriceDiscountStpType() == self::PRICE_DISCOUNT_STP_TYPE_SOLD_ON_BOTH;
    }

    public function getPriceDiscountStpAdditionalFlags()
    {
        $soldOnEbayFlag  = false;
        $soldOffEbayFlag = false;

        switch ($this->getPriceDiscountStpType()) {

            case self::PRICE_DISCOUNT_STP_TYPE_SOLD_ON_EBAY:
                $soldOnEbayFlag = true;
                break;

            case self::PRICE_DISCOUNT_STP_TYPE_SOLD_OFF_EBAY:
                $soldOffEbayFlag = true;
                break;

            case self::PRICE_DISCOUNT_STP_TYPE_SOLD_ON_BOTH:
                $soldOnEbayFlag  = true;
                $soldOffEbayFlag = true;
                break;
        }

        return array(
            'sold_on_ebay'  => $soldOnEbayFlag,
            'sold_off_ebay' => $soldOffEbayFlag
        );
    }

    //-------------------------

    public function getPriceDiscountMapMode()
    {
        return (int)$this->getData('price_discount_map_mode');
    }

    public function isPriceDiscountMapModeNone()
    {
        return $this->getPriceDiscountMapMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_NONE;
    }

    public function isPriceDiscountMapModeProduct()
    {
        return $this->getPriceDiscountMapMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_PRODUCT;
    }

    public function isPriceDiscountMapModeSpecial()
    {
        return $this->getPriceDiscountMapMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_SPECIAL;
    }

    public function isPriceDiscountMapModeAttribute()
    {
        return $this->getPriceDiscountMapMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_ATTRIBUTE;
    }

    public function getPriceDiscountMapSource()
    {
        return array(
            'mode'      => $this->getPriceDiscountMapMode(),
            'attribute' => $this->getData('price_discount_map_attribute')
        );
    }

    public function getPriceDiscountMapAttributes()
    {
        $attributes = array();
        $src = $this->getPriceDiscountMapSource();

        if ($src['mode'] == Ess_M2ePro_Model_Template_SellingFormat::PRICE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getPriceDiscountMapExposureType()
    {
        return (int)$this->getData('price_discount_map_exposure_type');
    }

    public function isPriceDiscountMapExposureTypeNone()
    {
        return $this->getPriceDiscountMapExposureType() == self::PRICE_DISCOUNT_MAP_EXPOSURE_NONE;
    }

    public function isPriceDiscountMapExposureTypeDuringCheckout()
    {
        return $this->getPriceDiscountMapExposureType() == self::PRICE_DISCOUNT_MAP_EXPOSURE_DURING_CHECKOUT;
    }

    public function isPriceDiscountMapExposureTypePreCheckout()
    {
        return $this->getPriceDiscountMapExposureType() == self::PRICE_DISCOUNT_MAP_EXPOSURE_PRE_CHECKOUT;
    }

    //-------------------------

    public function usesProductOrSpecialPrice()
    {
        if ($this->isListingTypeFixed()) {

            if ($this->isFixedPriceModeProduct() || $this->isFixedPriceModeSpecial()) {
                return true;
            }

            if ($this->isPriceDiscountStpModeProduct() || $this->isPriceDiscountStpModeSpecial()) {
                return true;
            }

            return false;
        }

        if ($this->isStartPriceModeProduct() || $this->isStartPriceModeSpecial()) {
            return true;
        }

        if ($this->isReservePriceModeProduct() || $this->isReservePriceModeSpecial()) {
            return true;
        }

        if ($this->isBuyItNowPriceModeProduct() || $this->isBuyItNowPriceModeSpecial()) {
            return true;
        }

        return false;
    }

    //-------------------------

    public function isBestOfferEnabled()
    {
        return (int)$this->getData('best_offer_mode') == self::BEST_OFFER_MODE_YES;
    }

    //-------------------------

    public function getBestOfferAcceptMode()
    {
        return (int)$this->getData('best_offer_accept_mode');
    }

    public function isBestOfferAcceptModeNo()
    {
        return $this->getBestOfferAcceptMode() == self::BEST_OFFER_ACCEPT_MODE_NO;
    }

    public function isBestOfferAcceptModePercentage()
    {
        return $this->getBestOfferAcceptMode() == self::BEST_OFFER_ACCEPT_MODE_PERCENTAGE;
    }

    public function isBestOfferAcceptModeAttribute()
    {
        return $this->getBestOfferAcceptMode() == self::BEST_OFFER_ACCEPT_MODE_ATTRIBUTE;
    }

    public function getBestOfferAcceptValue()
    {
        return $this->getData('best_offer_accept_value');
    }

    public function getBestOfferAcceptSource()
    {
        return array(
            'mode' => $this->getBestOfferAcceptMode(),
            'value' => $this->getBestOfferAcceptValue(),
            'attribute' => $this->getData('best_offer_accept_attribute')
        );
    }

    public function getBestOfferAcceptAttributes()
    {
        $attributes = array();
        $src = $this->getBestOfferAcceptSource();

        if ($src['mode'] == self::BEST_OFFER_ACCEPT_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getBestOfferRejectMode()
    {
        return (int)$this->getData('best_offer_reject_mode');
    }

    public function isBestOfferRejectModeNo()
    {
        return $this->getBestOfferRejectMode() == self::BEST_OFFER_REJECT_MODE_NO;
    }

    public function isBestOfferRejectModePercentage()
    {
        return $this->getBestOfferRejectMode() == self::BEST_OFFER_REJECT_MODE_PERCENTAGE;
    }

    public function isBestOfferRejectModeAttribute()
    {
        return $this->getBestOfferRejectMode() == self::BEST_OFFER_REJECT_MODE_ATTRIBUTE;
    }

    public function getBestOfferRejectValue()
    {
        return $this->getData('best_offer_reject_value');
    }

    public function getBestOfferRejectSource()
    {
        return array(
            'mode' => $this->getBestOfferRejectMode(),
            'value' => $this->getBestOfferRejectValue(),
            'attribute' => $this->getData('best_offer_reject_attribute')
        );
    }

    public function getBestOfferRejectAttributes()
    {
        $attributes = array();
        $src = $this->getBestOfferRejectSource();

        if ($src['mode'] == self::BEST_OFFER_REJECT_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    //-------------------------

    public function getCharity()
    {
        if (is_null($this->getData('charity'))) {
            return NULL;
        }

        return json_decode($this->getData('charity'), true);
    }

    //-------------------------

    public function isIgnoreVariationsEnabled()
    {
        return (bool)$this->getData('ignore_variations');
    }

    // #######################################

    public function getTrackingAttributes()
    {
        return array_unique(array_merge(
            $this->getQtyAttributes(),
            $this->getFixedPriceAttributes(),
            $this->getStartPriceAttributes(),
            $this->getReservePriceAttributes(),
            $this->getBuyItNowPriceAttributes()
        ));
    }

    public function getUsedAttributes()
    {
        return array_unique(array_merge(
            $this->getListingTypeAttributes(),
            $this->getDurationAttributes(),
            $this->getQtyAttributes(),
            $this->getFixedPriceAttributes(),
            $this->getStartPriceAttributes(),
            $this->getReservePriceAttributes(),
            $this->getBuyItNowPriceAttributes(),
            $this->getPriceDiscountStpAttributes(),
            $this->getPriceDiscountMapAttributes(),
            $this->getBestOfferAcceptAttributes(),
            $this->getBestOfferRejectAttributes()
        ));
    }

    // #######################################

    public function getDefaultSettingsSimpleMode()
    {
        return array(

            'listing_type' => self::LISTING_TYPE_FIXED,
            'listing_type_attribute' => '',

            'listing_is_private' => self::LISTING_IS_PRIVATE_NO,

            'duration_mode' => 3,
            'duration_attribute' => '',

            'out_of_stock_control' => 1,

            'qty_mode' => Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT,
            'qty_custom_value' => 1,
            'qty_custom_attribute' => '',
            'qty_percentage' => 100,
            'qty_modification_mode' => self::QTY_MODIFICATION_MODE_OFF,
            'qty_min_posted_value' => self::QTY_MIN_POSTED_DEFAULT_VALUE,
            'qty_max_posted_value' => self::QTY_MAX_POSTED_DEFAULT_VALUE,

            'vat_percent'    => 0,
            'tax_table_mode' => 0,

            'restricted_to_business' => self::RESTRICTED_TO_BUSINESS_DISABLED,

            'tax_category_mode'      => 0,
            'tax_category_value'     => '',
            'tax_category_attribute' => '',

            'price_increase_vat_percent' => 0,
            'price_variation_mode' => self::PRICE_VARIATION_MODE_PARENT,

            'fixed_price_mode' => Ess_M2ePro_Model_Template_SellingFormat::PRICE_PRODUCT,
            'fixed_price_coefficient' => '',
            'fixed_price_custom_attribute' => '',

            'start_price_mode' => Ess_M2ePro_Model_Template_SellingFormat::PRICE_PRODUCT,
            'start_price_coefficient' => '',
            'start_price_custom_attribute' => '',

            'reserve_price_mode' => Ess_M2ePro_Model_Template_SellingFormat::PRICE_NONE,
            'reserve_price_coefficient' => '',
            'reserve_price_custom_attribute' => '',

            'buyitnow_price_mode' => Ess_M2ePro_Model_Template_SellingFormat::PRICE_NONE,
            'buyitnow_price_coefficient' => '',
            'buyitnow_price_custom_attribute' => '',

            'price_discount_stp_mode' => Ess_M2ePro_Model_Template_SellingFormat::PRICE_NONE,
            'price_discount_stp_attribute' => '',
            'price_discount_stp_type' => self::PRICE_DISCOUNT_STP_TYPE_RRP,

            'price_discount_map_mode' => Ess_M2ePro_Model_Template_SellingFormat::PRICE_NONE,
            'price_discount_map_attribute' => '',
            'price_discount_map_exposure_type' => self::PRICE_DISCOUNT_MAP_EXPOSURE_NONE,

            'best_offer_mode' => self::BEST_OFFER_MODE_NO,

            'best_offer_accept_mode' => self::BEST_OFFER_ACCEPT_MODE_NO,
            'best_offer_accept_value' => '',
            'best_offer_accept_attribute' => '',

            'best_offer_reject_mode' => self::BEST_OFFER_REJECT_MODE_NO,
            'best_offer_reject_value' => '',
            'best_offer_reject_attribute' => '',

            'charity' => '',
            'ignore_variations' => 0
        );
    }

    public function getDefaultSettingsAdvancedMode()
    {
        return $this->getDefaultSettingsSimpleMode();
    }

    // #######################################

    /**
     * @param bool $asArrays
     * @param string|array $columns
     * @return array
     */
    public function getAffectedListingsProducts($asArrays = true, $columns = '*')
    {
        $templateManager = Mage::getModel('M2ePro/Ebay_Template_Manager');
        $templateManager->setTemplate(Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT);

        $listingsProducts = $templateManager->getAffectedOwnerObjects(
            Ess_M2ePro_Model_Ebay_Template_Manager::OWNER_LISTING_PRODUCT, $this->getId(), $asArrays, $columns
        );

        $listings = $templateManager->getAffectedOwnerObjects(
            Ess_M2ePro_Model_Ebay_Template_Manager::OWNER_LISTING, $this->getId(), false
        );

        foreach ($listings as $listing) {

            $tempListingsProducts = $listing->getChildObject()
                                            ->getAffectedListingsProductsByTemplate(
                                                Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT,
                                                $asArrays, $columns
                                            );

            foreach ($tempListingsProducts as $listingProduct) {
                if (!isset($listingsProducts[$listingProduct['id']])) {
                    $listingsProducts[$listingProduct['id']] = $listingProduct;
                }
            }
        }

        return $listingsProducts;
    }

    public function setSynchStatusNeed($newData, $oldData)
    {
        $listingsProducts = $this->getAffectedListingsProducts(true, array('id'));
        if (empty($listingsProducts)) {
            return;
        }

        $this->getResource()->setSynchStatusNeed($newData,$oldData,$listingsProducts);
    }

    // #######################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_sellingformat');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_sellingformat');
        return parent::delete();
    }

    // #######################################
}