<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Template_SellingFormat getParentObject()
 * @method Ess_M2ePro_Model_Resource_Ebay_Template_SellingFormat getResource()
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

    const VAT_MODE_NO = 0;
    const VAT_MODE_INCLUDING_IN_PRICE = 1;
    const VAT_MODE_ON_TOP_OF_PRICE = 2;

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

    const LOT_SIZE_MODE_DISABLED = 0;
    const LOT_SIZE_MODE_CUSTOM_VALUE = 1;
    const LOT_SIZE_MODE_ATTRIBUTE = 2;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_SellingFormat_Source[]
     */
    protected $_sellingSourceModels = array();

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_SellingFormat');
    }

    //########################################

    /**
     * @return string
     */
    public function getNick()
    {
        return Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SELLING_FORMAT;
    }

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::getModel('M2ePro/Ebay_Listing')
                            ->getCollection()
                            ->addFieldToFilter('template_selling_format_id', $this->getId())
                            ->getSize() ||
               (bool)Mage::getModel('M2ePro/Ebay_Listing_Product')
                            ->getCollection()
                            ->addFieldToFilter(
                                'template_selling_format_mode',
                                Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE
                            )
                            ->addFieldToFilter('template_selling_format_id', $this->getId())
                            ->getSize();
    }

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->_sellingSourceModels = array();

        $this->delete();
        return true;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Ebay_Template_SellingFormat_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->_sellingSourceModels[$productId])) {
            return $this->_sellingSourceModels[$productId];
        }

        $this->_sellingSourceModels[$productId] = Mage::getModel('M2ePro/Ebay_Template_SellingFormat_Source');
        $this->_sellingSourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->_sellingSourceModels[$productId]->setSellingFormatTemplate($this->getParentObject());

        return $this->_sellingSourceModels[$productId];
    }

    //########################################

    /**
     * @return int
     */
    public function getListingType()
    {
        return (int)$this->getData('listing_type');
    }

    /**
     * @return bool
     */
    public function isListingTypeFixed()
    {
        return $this->getListingType() == self::LISTING_TYPE_FIXED;
    }

    /**
     * @return bool
     */
    public function isListingTypeAuction()
    {
        return $this->getListingType() == self::LISTING_TYPE_AUCTION;
    }

    /**
     * @return bool
     */
    public function isListingTypeAttribute()
    {
        return $this->getListingType() == self::LISTING_TYPE_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getListingTypeSource()
    {
        return array(
            'mode'      => $this->getListingType(),
            'attribute' => $this->getData('listing_type_attribute')
        );
    }

    /**
     * @return array
     */
    public function getListingTypeAttributes()
    {
        $attributes = array();
        $src = $this->getListingTypeSource();

        if ($src['mode'] == self::LISTING_TYPE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getDurationMode()
    {
        return (int)$this->getData('duration_mode');
    }

    /**
     * @return array
     */
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

    /**
     * @return array
     */
    public function getDurationAttributes()
    {
        $attributes = array();
        $src = $this->getDurationSource();

        if ($src['mode'] == self::DURATION_TYPE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isPrivateListing()
    {
        return (bool)$this->getData('listing_is_private');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getQtyMode()
    {
        return (int)$this->getData('qty_mode');
    }

    /**
     * @return bool
     */
    public function isQtyModeProduct()
    {
        return $this->getQtyMode() == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isQtyModeNumber()
    {
        return $this->getQtyMode() == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_NUMBER;
    }

    /**
     * @return bool
     */
    public function isQtyModeAttribute()
    {
        return $this->getQtyMode() == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_ATTRIBUTE;
    }

    /**
     * @return bool
     */
    public function isQtyModeProductFixed()
    {
        return $this->getQtyMode() == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED;
    }

    /**
     * @return int
     */
    public function getQtyNumber()
    {
        return (int)$this->getData('qty_custom_value');
    }

    /**
     * @return array
     */
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

    /**
     * @return array
     */
    public function getQtyAttributes()
    {
        $attributes = array();
        $src = $this->getQtySource();

        if ($src['mode'] == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getQtyPercentage()
    {
        return (int)$this->getData('qty_percentage');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getQtyModificationMode()
    {
        return (int)$this->getData('qty_modification_mode');
    }

    /**
     * @return bool
     */
    public function isQtyModificationModeOn()
    {
        return $this->getQtyModificationMode() == self::QTY_MODIFICATION_MODE_ON;
    }

    /**
     * @return bool
     */
    public function isQtyModificationModeOff()
    {
        return $this->getQtyModificationMode() == self::QTY_MODIFICATION_MODE_OFF;
    }

    /**
     * @return int
     */
    public function getQtyMinPostedValue()
    {
        return (int)$this->getData('qty_min_posted_value');
    }

    /**
     * @return int
     */
    public function getQtyMaxPostedValue()
    {
        return (int)$this->getData('qty_max_posted_value');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getLotSizeMode()
    {
        return (int)$this->getData('lot_size_mode');
    }

    /**
     * @return bool
     */
    public function isLotSizeDisabled()
    {
        return $this->getLotSizeMode() == self::LOT_SIZE_MODE_DISABLED;
    }

    /**
     * @return bool
     */
    public function isLotSizeCustom()
    {
        return $this->getLotSizeMode() == self::LOT_SIZE_MODE_CUSTOM_VALUE;
    }

    /**
     * @return bool
     */
    public function isLotSizeAttribute()
    {
        return $this->getLotSizeMode() == self::LOT_SIZE_MODE_ATTRIBUTE;
    }

    /**
     * @return int
     */
    public function getLotSizeCustomValue()
    {
        return (int)$this->getData('lot_size_custom_value');
    }

    /**
     * @return array
     */
    public function getLotSizeSource()
    {
        return array(
            'mode'      => $this->getLotSizeMode(),
            'value'     => $this->getLotSizeCustomValue(),
            'attribute' => $this->getData('lot_size_attribute')
        );
    }

    /**
     * @return array
     */
    public function getLotSizeAttributes()
    {
        $attributes = array();
        $src = $this->getLotSizeSource();

        if ($src['mode'] == self::LOT_SIZE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getVatMode()
    {
        return (int)$this->getData('vat_mode');
    }

    /**
     * @return int
     */
    public function isVatModeEnabled()
    {
        return $this->getVatMode() != self::VAT_MODE_NO;
    }

    /**
     * @return bool
     */
    public function isVatModeOnTopOfPrice()
    {
        return $this->getVatMode() == self::VAT_MODE_ON_TOP_OF_PRICE;
    }

    /**
     * @return float
     */
    public function getVatPercent()
    {
        return (float)$this->getData('vat_percent');
    }

    /**
     * @return bool
     */
    public function isTaxTableEnabled()
    {
        return (bool)$this->getData('tax_table_mode');
    }

    /**
     * @return array
     */
    public function getTaxCategorySource()
    {
        return array(
            'mode'      => $this->getData('tax_category_mode'),
            'value'     => $this->getData('tax_category_value'),
            'attribute' => $this->getData('tax_category_attribute')
        );
    }

    /**
     * @return array
     */
    public function getTaxCategoryAttributes()
    {
        $attributes = array();
        $src = $this->getTaxCategorySource();

        if ($src['mode'] == self::TAX_CATEGORY_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    /**
     * @return bool
     */
    public function isRestrictedToBusinessEnabled()
    {
        return (bool)$this->getData('restricted_to_business');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getPriceVariationMode()
    {
        return (int)$this->getData('price_variation_mode');
    }

    /**
     * @return bool
     */
    public function isPriceVariationModeParent()
    {
        return $this->getPriceVariationMode() == self::PRICE_VARIATION_MODE_PARENT;
    }

    /**
     * @return bool
     */
    public function isPriceVariationModeChildren()
    {
        return $this->getPriceVariationMode() == self::PRICE_VARIATION_MODE_CHILDREN;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getFixedPriceMode()
    {
        return (int)$this->getData('fixed_price_mode');
    }

    /**
     * @return bool
     */
    public function isFixedPriceModeNone()
    {
        return $this->getFixedPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isFixedPriceModeProduct()
    {
        return $this->getFixedPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isFixedPriceModeSpecial()
    {
        return $this->getFixedPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_SPECIAL;
    }

    /**
     * @return bool
     */
    public function isFixedPriceModeAttribute()
    {
        return $this->getFixedPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE;
    }

    public function getFixedPriceCoefficient()
    {
        return $this->getData('fixed_price_coefficient');
    }

    /**
     * @return array
     */
    public function getFixedPriceSource()
    {
        return array(
            'mode'        => $this->getFixedPriceMode(),
            'coefficient' => $this->getFixedPriceCoefficient(),
            'attribute'   => $this->getData('fixed_price_custom_attribute')
        );
    }

    /**
     * @return array
     */
    public function getFixedPriceAttributes()
    {
        $attributes = array();
        $src = $this->getFixedPriceSource();

        if ($src['mode'] == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getStartPriceMode()
    {
        return (int)$this->getData('start_price_mode');
    }

    /**
     * @return bool
     */
    public function isStartPriceModeNone()
    {
        return $this->getStartPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isStartPriceModeProduct()
    {
        return $this->getStartPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isStartPriceModeSpecial()
    {
        return $this->getStartPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_SPECIAL;
    }

    /**
     * @return bool
     */
    public function isStartPriceModeAttribute()
    {
        return $this->getStartPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE;
    }

    public function getStartPriceCoefficient()
    {
        return $this->getData('start_price_coefficient');
    }

    /**
     * @return array
     */
    public function getStartPriceSource()
    {
        return array(
            'mode'        => $this->getStartPriceMode(),
            'coefficient' => $this->getStartPriceCoefficient(),
            'attribute'   => $this->getData('start_price_custom_attribute')
        );
    }

    /**
     * @return array
     */
    public function getStartPriceAttributes()
    {
        $attributes = array();
        $src = $this->getStartPriceSource();

        if ($src['mode'] == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getReservePriceMode()
    {
        return (int)$this->getData('reserve_price_mode');
    }

    /**
     * @return bool
     */
    public function isReservePriceModeNone()
    {
        return $this->getReservePriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isReservePriceModeProduct()
    {
        return $this->getReservePriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isReservePriceModeSpecial()
    {
        return $this->getReservePriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_SPECIAL;
    }

    /**
     * @return bool
     */
    public function isReservePriceModeAttribute()
    {
        return $this->getReservePriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE;
    }

    public function getReservePriceCoefficient()
    {
        return $this->getData('reserve_price_coefficient');
    }

    /**
     * @return array
     */
    public function getReservePriceSource()
    {
        return array(
            'mode'        => $this->getReservePriceMode(),
            'coefficient' => $this->getReservePriceCoefficient(),
            'attribute'   => $this->getData('reserve_price_custom_attribute')
        );
    }

    /**
     * @return array
     */
    public function getReservePriceAttributes()
    {
        $attributes = array();
        $src = $this->getReservePriceSource();

        if ($src['mode'] == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getBuyItNowPriceMode()
    {
        return (int)$this->getData('buyitnow_price_mode');
    }

    /**
     * @return bool
     */
    public function isBuyItNowPriceModeNone()
    {
        return $this->getBuyItNowPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isBuyItNowPriceModeProduct()
    {
        return $this->getBuyItNowPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isBuyItNowPriceModeSpecial()
    {
        return $this->getBuyItNowPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_SPECIAL;
    }

    /**
     * @return bool
     */
    public function isBuyItNowPriceModeAttribute()
    {
        return $this->getBuyItNowPriceMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE;
    }

    public function getBuyItNowPriceCoefficient()
    {
        return $this->getData('buyitnow_price_coefficient');
    }

    /**
     * @return array
     */
    public function getBuyItNowPriceSource()
    {
        return array(
            'mode'      => $this->getBuyItNowPriceMode(),
            'coefficient' => $this->getBuyItNowPriceCoefficient(),
            'attribute' => $this->getData('buyitnow_price_custom_attribute')
        );
    }

    /**
     * @return array
     */
    public function getBuyItNowPriceAttributes()
    {
        $attributes = array();
        $src = $this->getBuyItNowPriceSource();

        if ($src['mode'] == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getPriceDiscountStpMode()
    {
        return (int)$this->getData('price_discount_stp_mode');
    }

    /**
     * @return bool
     */
    public function isPriceDiscountStpModeNone()
    {
        return $this->getPriceDiscountStpMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isPriceDiscountStpModeProduct()
    {
        return $this->getPriceDiscountStpMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isPriceDiscountStpModeSpecial()
    {
        return $this->getPriceDiscountStpMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_SPECIAL;
    }

    /**
     * @return bool
     */
    public function isPriceDiscountStpModeAttribute()
    {
        return $this->getPriceDiscountStpMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getPriceDiscountStpSource()
    {
        return array(
            'mode'      => $this->getPriceDiscountStpMode(),
            'attribute' => $this->getData('price_discount_stp_attribute')
        );
    }

    /**
     * @return array
     */
    public function getPriceDiscountStpAttributes()
    {
        $attributes = array();
        $src = $this->getPriceDiscountStpSource();

        if ($src['mode'] == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getPriceDiscountStpType()
    {
        return (int)$this->getData('price_discount_stp_type');
    }

    /**
     * @return bool
     */
    public function isPriceDiscountStpTypeRrp()
    {
        return $this->getPriceDiscountStpType() == self::PRICE_DISCOUNT_STP_TYPE_RRP;
    }

    /**
     * @return bool
     */
    public function isPriceDiscountStpTypeSoldOnEbay()
    {
        return $this->getPriceDiscountStpType() == self::PRICE_DISCOUNT_STP_TYPE_SOLD_ON_EBAY;
    }

    /**
     * @return bool
     */
    public function isPriceDiscountStpTypeSoldOffEbay()
    {
        return $this->getPriceDiscountStpType() == self::PRICE_DISCOUNT_STP_TYPE_SOLD_OFF_EBAY;
    }

    /**
     * @return bool
     */
    public function isPriceDiscountStpTypeSoldOnBoth()
    {
        return $this->getPriceDiscountStpType() == self::PRICE_DISCOUNT_STP_TYPE_SOLD_ON_BOTH;
    }

    /**
     * @return array
     */
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

    // ---------------------------------------

    /**
     * @return int
     */
    public function getPriceDiscountMapMode()
    {
        return (int)$this->getData('price_discount_map_mode');
    }

    /**
     * @return bool
     */
    public function isPriceDiscountMapModeNone()
    {
        return $this->getPriceDiscountMapMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isPriceDiscountMapModeProduct()
    {
        return $this->getPriceDiscountMapMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_PRODUCT;
    }

    /**
     * @return bool
     */
    public function isPriceDiscountMapModeSpecial()
    {
        return $this->getPriceDiscountMapMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_SPECIAL;
    }

    /**
     * @return bool
     */
    public function isPriceDiscountMapModeAttribute()
    {
        return $this->getPriceDiscountMapMode() == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE;
    }

    /**
     * @return array
     */
    public function getPriceDiscountMapSource()
    {
        return array(
            'mode'      => $this->getPriceDiscountMapMode(),
            'attribute' => $this->getData('price_discount_map_attribute')
        );
    }

    /**
     * @return array
     */
    public function getPriceDiscountMapAttributes()
    {
        $attributes = array();
        $src = $this->getPriceDiscountMapSource();

        if ($src['mode'] == Ess_M2ePro_Model_Template_SellingFormat::PRICE_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getPriceDiscountMapExposureType()
    {
        return (int)$this->getData('price_discount_map_exposure_type');
    }

    /**
     * @return bool
     */
    public function isPriceDiscountMapExposureTypeNone()
    {
        return $this->getPriceDiscountMapExposureType() == self::PRICE_DISCOUNT_MAP_EXPOSURE_NONE;
    }

    /**
     * @return bool
     */
    public function isPriceDiscountMapExposureTypeDuringCheckout()
    {
        return $this->getPriceDiscountMapExposureType() == self::PRICE_DISCOUNT_MAP_EXPOSURE_DURING_CHECKOUT;
    }

    /**
     * @return bool
     */
    public function isPriceDiscountMapExposureTypePreCheckout()
    {
        return $this->getPriceDiscountMapExposureType() == self::PRICE_DISCOUNT_MAP_EXPOSURE_PRE_CHECKOUT;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function usesConvertiblePrices()
    {
        $isPriceConvertEnabled = Mage::helper('M2ePro/Module_Configuration')
            ->isEnableMagentoAttributePriceTypeConvertingMode();

        $attributeHelper = Mage::helper('M2ePro/Magento_Attribute');

        if ($this->isListingTypeFixed() || $this->isListingTypeAttribute()) {
            if ($this->isFixedPriceModeProduct() || $this->isFixedPriceModeSpecial()) {
                return true;
            }

            if ($this->isPriceDiscountStpModeProduct() || $this->isPriceDiscountStpModeSpecial()) {
                return true;
            }

            if ($this->isPriceDiscountMapModeProduct() || $this->isPriceDiscountMapModeSpecial()) {
                return true;
            }

            if ($isPriceConvertEnabled) {
                if ($this->isFixedPriceModeAttribute() &&
                    $attributeHelper->isAttributeInputTypePrice($this->getData('fixed_price_custom_attribute'))) {
                    return true;
                }

                if ($this->isPriceDiscountStpModeAttribute() &&
                    $attributeHelper->isAttributeInputTypePrice($this->getData('price_discount_stp_attribute'))) {
                    return true;
                }

                if ($this->isPriceDiscountMapModeAttribute() &&
                    $attributeHelper->isAttributeInputTypePrice($this->getData('price_discount_map_attribute'))) {
                    return true;
                }
            }

            if ($this->isListingTypeFixed()) {
                return false;
            }
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

        if ($isPriceConvertEnabled) {
            if ($this->isStartPriceModeAttribute() &&
                $attributeHelper->isAttributeInputTypePrice($this->getData('start_price_custom_attribute'))) {
                return true;
            }

            if ($this->isReservePriceModeAttribute() &&
                $attributeHelper->isAttributeInputTypePrice($this->getData('reserve_price_custom_attribute'))) {
                return true;
            }

            if ($this->isBuyItNowPriceModeAttribute() &&
                $attributeHelper->isAttributeInputTypePrice($this->getData('buyitnow_price_custom_attribute'))) {
                return true;
            }
        }

        if ($this->isBestOfferEnabled()) {
            if ($this->isBestOfferAcceptModePercentage() || $this->isBestOfferRejectModePercentage()) {
                return true;
            }

            if ($isPriceConvertEnabled) {
                if ($this->isBestOfferAcceptModeAttribute() &&
                    $attributeHelper->isAttributeInputTypePrice($this->getData('best_offer_accept_attribute'))) {
                    return true;
                }

                if ($this->isBestOfferRejectModeAttribute() &&
                    $attributeHelper->isAttributeInputTypePrice($this->getData('best_offer_reject_attribute'))) {
                    return true;
                }
            }
        }

        return false;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isBestOfferEnabled()
    {
        return (int)$this->getData('best_offer_mode') == self::BEST_OFFER_MODE_YES;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getBestOfferAcceptMode()
    {
        return (int)$this->getData('best_offer_accept_mode');
    }

    /**
     * @return bool
     */
    public function isBestOfferAcceptModeNo()
    {
        return $this->getBestOfferAcceptMode() == self::BEST_OFFER_ACCEPT_MODE_NO;
    }

    /**
     * @return bool
     */
    public function isBestOfferAcceptModePercentage()
    {
        return $this->getBestOfferAcceptMode() == self::BEST_OFFER_ACCEPT_MODE_PERCENTAGE;
    }

    /**
     * @return bool
     */
    public function isBestOfferAcceptModeAttribute()
    {
        return $this->getBestOfferAcceptMode() == self::BEST_OFFER_ACCEPT_MODE_ATTRIBUTE;
    }

    public function getBestOfferAcceptValue()
    {
        return $this->getData('best_offer_accept_value');
    }

    /**
     * @return array
     */
    public function getBestOfferAcceptSource()
    {
        return array(
            'mode' => $this->getBestOfferAcceptMode(),
            'value' => $this->getBestOfferAcceptValue(),
            'attribute' => $this->getData('best_offer_accept_attribute')
        );
    }

    /**
     * @return array
     */
    public function getBestOfferAcceptAttributes()
    {
        $attributes = array();
        $src = $this->getBestOfferAcceptSource();

        if ($src['mode'] == self::BEST_OFFER_ACCEPT_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getBestOfferRejectMode()
    {
        return (int)$this->getData('best_offer_reject_mode');
    }

    /**
     * @return bool
     */
    public function isBestOfferRejectModeNo()
    {
        return $this->getBestOfferRejectMode() == self::BEST_OFFER_REJECT_MODE_NO;
    }

    /**
     * @return bool
     */
    public function isBestOfferRejectModePercentage()
    {
        return $this->getBestOfferRejectMode() == self::BEST_OFFER_REJECT_MODE_PERCENTAGE;
    }

    /**
     * @return bool
     */
    public function isBestOfferRejectModeAttribute()
    {
        return $this->getBestOfferRejectMode() == self::BEST_OFFER_REJECT_MODE_ATTRIBUTE;
    }

    public function getBestOfferRejectValue()
    {
        return $this->getData('best_offer_reject_value');
    }

    /**
     * @return array
     */
    public function getBestOfferRejectSource()
    {
        return array(
            'mode' => $this->getBestOfferRejectMode(),
            'value' => $this->getBestOfferRejectValue(),
            'attribute' => $this->getData('best_offer_reject_attribute')
        );
    }

    /**
     * @return array
     */
    public function getBestOfferRejectAttributes()
    {
        $attributes = array();
        $src = $this->getBestOfferRejectSource();

        if ($src['mode'] == self::BEST_OFFER_REJECT_MODE_ATTRIBUTE) {
            $attributes[] = $src['attribute'];
        }

        return $attributes;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isIgnoreVariationsEnabled()
    {
        return (bool)$this->getData('ignore_variations');
    }

    //########################################

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

    //########################################
}
