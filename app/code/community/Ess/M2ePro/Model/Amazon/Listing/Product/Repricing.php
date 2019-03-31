<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Repricing extends Ess_M2ePro_Model_Component_Abstract
{
    /** @var Ess_M2ePro_Model_Listing_Product $listingProductModel */
    private $listingProductModel = NULL;

    private $regularPriceCache = NULL;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Listing_Product_Repricing');
    }

    //########################################

    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->listingProductModel = $listingProduct;
        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        if (!is_null($this->listingProductModel)) {
            return $this->listingProductModel;
        }

        return $this->listingProductModel = Mage::helper('M2ePro/Component_Amazon')->getObject(
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
        if (!is_null($this->regularPriceCache)) {
            return $this->regularPriceCache;
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

        if ($source['mode'] == Ess_M2ePro_Model_Amazon_Account_Repricing::REGULAR_PRICE_MODE_PRODUCT_POLICY) {
            $amazonSellingFormatTemplate = $this->getAmazonListingProduct()->getAmazonSellingFormatTemplate();

            $source            = $amazonSellingFormatTemplate->getRegularPriceSource();
            $sourceModeMapping = NULL;
            $coefficient       = $amazonSellingFormatTemplate->getRegularPriceCoefficient();
            $variationMode     = $amazonSellingFormatTemplate->getRegularPriceVariationMode();
        }

        $calculator = $this->getPriceCalculator($source, $sourceModeMapping, $coefficient, $variationMode);

        if ($this->getVariationManager()->isPhysicalUnit() &&
            $this->getVariationManager()->getTypeModel()->isVariationProductMatched()) {

            $variations = $this->getListingProduct()->getVariations(true);
            if (count($variations) <= 0) {
                throw new Ess_M2ePro_Model_Exception_Logic(
                    'There are no variations for a variation product.',
                    array(
                        'listing_product_id' => $this->getId()
                    )
                );
            }

            $variation = reset($variations);

            return $this->regularPriceCache = $calculator->getVariationValue($variation);
        }

        return $this->regularPriceCache = $calculator->getProductValue();
    }

    public function getMinPrice()
    {
        $source = $this->getAccountRepricing()->getMinPriceSource();

        if ($this->getAccountRepricing()->isMinPriceModeRegularPercent() ||
            $this->getAccountRepricing()->isMinPriceModeRegularValue()
        ) {
            if ($this->getAccountRepricing()->isRegularPriceModeManual()) {
                return NULL;
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
            if (count($variations) <= 0) {
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

        if ($this->getAccountRepricing()->isMaxPriceModeRegularPercent() ||
            $this->getAccountRepricing()->isMaxPriceModeRegularValue()
        ) {
            if ($this->getAccountRepricing()->isRegularPriceModeManual()) {
                return NULL;
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
            if (count($variations) <= 0) {
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

    //########################################

    public function isDisabled()
    {
        $source = $this->getAccountRepricing()->getDisableSource();

        if ($source['mode'] == Ess_M2ePro_Model_Amazon_Account_Repricing::DISABLE_MODE_MANUAL) {
            return NULL;
        }

        if ($source['mode'] == Ess_M2ePro_Model_Amazon_Account_Repricing::DISABLE_MODE_ATTRIBUTE) {
            return filter_var(
                $this->getActualMagentoProduct()->getAttributeValue($source['attribute']), FILTER_VALIDATE_BOOLEAN
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
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Repricing_PriceCalculator
     */
    private function getPriceCalculator(
        array $source,
        $sourceModeMapping = NULL,
        $coefficient = NULL,
        $priceVariationMode = NULL
    ) {
        /** @var $calculator Ess_M2ePro_Model_Amazon_Listing_Product_Repricing_PriceCalculator */
        $calculator = Mage::getModel('M2ePro/Amazon_Listing_Product_Repricing_PriceCalculator');
        !is_null($sourceModeMapping) && $calculator->setSourceModeMapping($sourceModeMapping);
        $calculator->setSource($source)->setProduct($this->getListingProduct());
        $calculator->setCoefficient($coefficient);
        $calculator->setPriceVariationMode($priceVariationMode);

        return $calculator;
    }

    private function calculateModificationValueBasedOnRegular(array $source)
    {
        $regularPrice = $this->getRegularPrice();
        if (empty($regularPrice)) {
            return NULL;
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

        return $value;
    }

    //########################################
}