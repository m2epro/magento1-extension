<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Repricing extends Ess_M2ePro_Model_Component_Abstract
{
    /** @var Ess_M2ePro_Model_Listing_Product $_listingProductModel */
    protected $_listingProductModel = null;

    protected $_regularPriceCache = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Listing_Product_Repricing');
    }

    //########################################

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->_listingProductModel = $listingProduct;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        if ($this->_listingProductModel !== null) {
            return $this->_listingProductModel;
        }

        return $this->_listingProductModel = Mage::helper('M2ePro/Component_Amazon')->getObject(
            'Listing_Product', $this->getListingProductId()
        );
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getAmazonListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    public function getMagentoProduct()
    {
        return $this->getAmazonListingProduct()->getMagentoProduct();
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getActualMagentoProduct()
    {
        return $this->getAmazonListingProduct()->getActualMagentoProduct();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager
     */
    public function getVariationManager()
    {
        return $this->getAmazonListingProduct()->getVariationManager();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Account_Repricing
     */
    public function getAccountRepricing()
    {
        return $this->getAmazonListingProduct()->getAmazonAccount()->getRepricing();
    }

    //########################################

    /**
     * @return int
     */
    public function getListingProductId()
    {
        return (int)$this->getData('listing_product_id');
    }

    /**
     * @return bool
     */
    public function isOnlineDisabled()
    {
        return (bool)$this->getData('is_online_disabled');
    }

    /**
     * @return bool
     */
    public function isOnlineInactive()
    {
        return (bool)$this->getData('is_online_inactive');
    }

    /**
     * @return bool
     */
    public function isOnlineManaged()
    {
        return !$this->isOnlineDisabled() && !$this->isOnlineInactive();
    }

    /**
     * @return float|int
     */
    public function getOnlineRegularPrice()
    {
        return $this->getData('online_regular_price');
    }

    /**
     * @return float|int
     */
    public function getOnlineMinPrice()
    {
        return $this->getData('online_min_price');
    }

    /**
     * @return float|int
     */
    public function getOnlineMaxPrice()
    {
        return $this->getData('online_max_price');
    }

    /**
     * @return bool
     */
    public function isProcessRequired()
    {
        return (bool)$this->getData('is_process_required');
    }

    //########################################

    /**
     * @return float|int
     */
    public function getLastUpdatedRegularPrice()
    {
        return $this->getData('last_updated_regular_price');
    }

    /**
     * @return float|int
     */
    public function getLastUpdatedMinPrice()
    {
        return $this->getData('last_updated_min_price');
    }

    /**
     * @return float|int
     */
    public function getLastUpdatedMaxPrice()
    {
        return $this->getData('last_updated_max_price');
    }

    /**
     * @return bool
     */
    public function  getLastUpdatedIsDisabled()
    {
        return (bool)$this->getData('last_updated_is_disabled');
    }

    //########################################

    public function getRegularPrice()
    {
        if ($this->_regularPriceCache !== null) {
            return $this->_regularPriceCache;
        }

        $source        = $this->getAccountRepricing()->getRegularPriceSource();
        $sourceModeMapping = array(
            Ess_M2ePro_Model_Listing_Product_PriceCalculator::MODE_NONE
                => Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_MODE_MANUAL,
            Ess_M2ePro_Model_Listing_Product_PriceCalculator::MODE_PRODUCT
                => Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_MODE_PRODUCT,
            Ess_M2ePro_Model_Listing_Product_PriceCalculator::MODE_ATTRIBUTE
                => Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_MODE_ATTRIBUTE,
            Ess_M2ePro_Model_Listing_Product_PriceCalculator::MODE_SPECIAL
                => Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_MODE_SPECIAL,
        );
        $coefficient   = $this->getAccountRepricing()->getRegularPriceCoefficient();
        $variationMode = $this->getAccountRepricing()->getRegularPriceVariationMode();
        $vatPercent    = null;

        if ($source['mode'] == Ess_M2ePro_Model_Amazon_Account_Repricing::REGULAR_PRICE_MODE_PRODUCT_POLICY) {
            $amazonSellingFormatTemplate = $this->getAmazonListingProduct()->getAmazonSellingFormatTemplate();

            $source            = $amazonSellingFormatTemplate->getRegularPriceSource();
            $sourceModeMapping = null;
            $coefficient       = $amazonSellingFormatTemplate->getRegularPriceCoefficient();
            $variationMode     = $amazonSellingFormatTemplate->getRegularPriceVariationMode();
            $vatPercent        = $amazonSellingFormatTemplate->getRegularPriceVatPercent();
        }

        $calculator = $this->getPriceCalculator(
            $source,
            $sourceModeMapping,
            $coefficient,
            $variationMode,
            $vatPercent
        );

        if ($this->getVariationManager()->isPhysicalUnit() &&
            $this->getVariationManager()->getTypeModel()->isVariationProductMatched()) {
            $variations = $this->getListingProduct()->getVariations(true);
            if (empty($variations)) {
                throw new Ess_M2ePro_Model_Exception_Logic(
                    'There are no variations for a variation product.',
                    array(
                        'listing_product_id' => $this->getId()
                    )
                );
            }

            $variation = reset($variations);

            return $this->_regularPriceCache = $calculator->getVariationValue($variation);
        }

        return $this->_regularPriceCache = $calculator->getProductValue();
    }

    public function getMinPrice()
    {
        $source = $this->getAccountRepricing()->getMinPriceSource();

        if ($this->getAccountRepricing()->isMinPriceModeRegularPercent()
            || $this->getAccountRepricing()->isMinPriceModeRegularValue()
            || $this->getAccountRepricing()->isMinPriceModeRegularValueAttribute()
            || $this->getAccountRepricing()->isMinPriceModeRegularPercentAttribute()
        ) {
            if ($this->getAccountRepricing()->isRegularPriceModeManual()) {
                return null;
            }

            $value = $this->getRegularPrice() - $this->calculateModificationValueBasedOnRegular($source);

            return $value <= 0 ? 0 : (float)$value;
        }

        $sourceModeMapping = array(
            Ess_M2ePro_Model_Listing_Product_PriceCalculator::MODE_NONE
                => Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_MODE_MANUAL,
            Ess_M2ePro_Model_Listing_Product_PriceCalculator::MODE_PRODUCT
                => Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_MODE_PRODUCT,
            Ess_M2ePro_Model_Listing_Product_PriceCalculator::MODE_ATTRIBUTE
                => Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_MODE_ATTRIBUTE,
            Ess_M2ePro_Model_Listing_Product_PriceCalculator::MODE_SPECIAL
                => Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_MODE_SPECIAL,
        );

        $calculator = $this->getPriceCalculator(
            $source,
            $sourceModeMapping,
            $this->getAccountRepricing()->getMinPriceCoefficient(),
            $this->getAccountRepricing()->getMinPriceVariationMode()
        );

        if ($this->getVariationManager()->isPhysicalUnit() &&
            $this->getVariationManager()->getTypeModel()->isVariationProductMatched()) {
            $variations = $this->getListingProduct()->getVariations(true);
            if (empty($variations)) {
                throw new Ess_M2ePro_Model_Exception_Logic(
                    'There are no variations for a variation product.',
                    array(
                        'listing_product_id' => $this->getId()
                    )
                );
            }

            $variation = reset($variations);

            return $calculator->getVariationValue($variation);
        }

        return $calculator->getProductValue();
    }

    public function getMaxPrice()
    {
        $source = $this->getAccountRepricing()->getMaxPriceSource();

        if ($this->getAccountRepricing()->isMaxPriceModeRegularPercent()
            || $this->getAccountRepricing()->isMaxPriceModeRegularValue()
            || $this->getAccountRepricing()->isMaxPriceModeRegularValueAttribute()
            || $this->getAccountRepricing()->isMaxPriceModeRegularPercentAttribute()
        ) {
            if ($this->getAccountRepricing()->isRegularPriceModeManual()) {
                return null;
            }

            $value = $this->getRegularPrice() + $this->calculateModificationValueBasedOnRegular($source);

            return $value <= 0 ? 0 : (float)$value;
        }

        $sourceModeMapping = array(
            Ess_M2ePro_Model_Listing_Product_PriceCalculator::MODE_NONE
                => Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_MODE_MANUAL,
            Ess_M2ePro_Model_Listing_Product_PriceCalculator::MODE_PRODUCT
                => Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_MODE_PRODUCT,
            Ess_M2ePro_Model_Listing_Product_PriceCalculator::MODE_ATTRIBUTE
                => Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_MODE_ATTRIBUTE,
            Ess_M2ePro_Model_Listing_Product_PriceCalculator::MODE_SPECIAL
                => Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_MODE_SPECIAL,
        );

        $calculator = $this->getPriceCalculator(
            $source,
            $sourceModeMapping,
            $this->getAccountRepricing()->getMaxPriceCoefficient(),
            $this->getAccountRepricing()->getMaxPriceVariationMode()
        );

        if ($this->getVariationManager()->isPhysicalUnit() &&
            $this->getVariationManager()->getTypeModel()->isVariationProductMatched()) {
            $variations = $this->getListingProduct()->getVariations(true);
            if (empty($variations)) {
                throw new Ess_M2ePro_Model_Exception_Logic(
                    'There are no variations for a variation product.',
                    array(
                        'listing_product_id' => $this->getId(),
                    )
                );
            }

            $variation = reset($variations);

            return $calculator->getVariationValue($variation);
        }

        return $calculator->getProductValue();
    }

    //########################################

    public function isDisabled()
    {
        $source = $this->getAccountRepricing()->getDisableSource();

        if ($source['mode'] == Ess_M2ePro_Model_Amazon_Account_Repricing::DISABLE_MODE_MANUAL) {
            return null;
        }

        if ($source['mode'] == Ess_M2ePro_Model_Amazon_Account_Repricing::DISABLE_MODE_ATTRIBUTE) {
            return filter_var(
                $this->getActualMagentoProduct()->getAttributeValue($source['attribute'], false),
                FILTER_VALIDATE_BOOLEAN
            );
        }

        $isDisabled = !$this->getAmazonListingProduct()->getMagentoProduct()->isStatusEnabled();
        if ($isDisabled) {
            return $isDisabled;
        }

        if ($this->getMagentoProduct()->isSimpleType() ||
            $this->getMagentoProduct()->isBundleType() ||
            $this->getMagentoProduct()->isDownloadableType()
        ) {
            return $isDisabled;
        }

        return !$this->getActualMagentoProduct()->isStatusEnabled();
    }

    //########################################

    /**
     * @param array $source
     * @param array $sourceModeMapping
     * @param string $coefficient
     * @param int $priceVariationMode
     * @param int $vatPercent
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Repricing_PriceCalculator
     */
    protected function getPriceCalculator(
        array $source,
        $sourceModeMapping = null,
        $coefficient = null,
        $priceVariationMode = null,
        $vatPercent = null
    ) {
        /** @var $calculator Ess_M2ePro_Model_Amazon_Listing_Product_Repricing_PriceCalculator */
        $calculator = Mage::getModel('M2ePro/Amazon_Listing_Product_Repricing_PriceCalculator');
        $sourceModeMapping !== null && $calculator->setSourceModeMapping($sourceModeMapping);
        $calculator->setSource($source)->setProduct($this->getListingProduct());
        $calculator->setCoefficient($coefficient);
        $calculator->setPriceVariationMode($priceVariationMode);
        $calculator->setVatPercent($vatPercent);

        return $calculator;
    }

    protected function calculateModificationValueBasedOnRegular(array $source)
    {
        $regularPrice = $this->getRegularPrice();
        if (empty($regularPrice)) {
            return null;
        }

        $value = 0;

        if ($source['mode'] == Ess_M2ePro_Model_Amazon_Account_Repricing::MAX_PRICE_MODE_REGULAR_VALUE &&
            $source['mode'] == Ess_M2ePro_Model_Amazon_Account_Repricing::MIN_PRICE_MODE_REGULAR_VALUE
        ) {
            $value = $source['regular_value'];
        }

        if ($source['mode'] == Ess_M2ePro_Model_Amazon_Account_Repricing::MAX_PRICE_MODE_REGULAR_PERCENT &&
            $source['mode'] == Ess_M2ePro_Model_Amazon_Account_Repricing::MIN_PRICE_MODE_REGULAR_PERCENT
        ) {
            $value = round($regularPrice * ((int)$source['regular_percent'] / 100), 2);
        }

        if ($source['mode'] == Ess_M2ePro_Model_Amazon_Account_Repricing::MAX_PRICE_MODE_REGULAR_VALUE_ATTRIBUTE
            || $source['mode'] == Ess_M2ePro_Model_Amazon_Account_Repricing::MIN_PRICE_MODE_REGULAR_VALUE_ATTRIBUTE
        ) {
            $attributeValue = $this->getValueFromAttribute($source['value_attribute']);
            if ($attributeValue > 0) {
                $value = round($attributeValue, 2);
            }
        }

        if ($source['mode'] == Ess_M2ePro_Model_Amazon_Account_Repricing::MAX_PRICE_MODE_REGULAR_PERCENT_ATTRIBUTE
            || $source['mode'] == Ess_M2ePro_Model_Amazon_Account_Repricing::MIN_PRICE_MODE_REGULAR_PERCENT_ATTRIBUTE
        ) {
            $attributeValue = $this->getValueFromAttribute($source['percent_attribute']);
            if ($attributeValue > 0) {
                $value = round($regularPrice * ($attributeValue / 100), 2);
            }
        }

        return (float)$value;
    }

    /**
     * @param string $attribute
     * @return float
     */
    protected function getValueFromAttribute($attribute)
    {
        return (float)$this->getMagentoProduct()->getAttributeValue($attribute);
    }

    //########################################
}
