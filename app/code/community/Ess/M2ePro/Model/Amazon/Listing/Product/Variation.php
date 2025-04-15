<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Listing_Product_Variation getParentObject()
 */
class Ess_M2ePro_Model_Amazon_Listing_Product_Variation extends Ess_M2ePro_Model_Component_Child_Amazon_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Listing_Product_Variation');
    }

    //########################################

    protected function _afterSave()
    {
        Mage::helper('M2ePro/Data_Cache_Runtime')->removeTagValues(
            "listing_product_{$this->getListingProduct()->getId()}_variations"
        );
        return parent::_afterSave();
    }

    protected function _beforeDelete()
    {
        Mage::helper('M2ePro/Data_Cache_Runtime')->removeTagValues(
            "listing_product_{$this->getListingProduct()->getId()}_variations"
        );
        return parent::_beforeDelete();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        return $this->getParentObject()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Account
     */
    public function getAmazonAccount()
    {
        return $this->getAccount()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        return $this->getParentObject()->getMarketplace();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Marketplace
     */
    public function getAmazonMarketplace()
    {
        return $this->getMarketplace()->getChildObject();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     */
    public function getListing()
    {
        return $this->getParentObject()->getListing();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing
     */
    public function getAmazonListing()
    {
        return $this->getListing()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        return $this->getParentObject()->getListingProduct();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product
     */
    public function getAmazonListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->getAmazonListingProduct()->getSellingFormatTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_SellingFormat
     */
    public function getAmazonSellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        return $this->getAmazonListingProduct()->getSynchronizationTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Synchronization
     */
    public function getAmazonSynchronizationTemplate()
    {
        return $this->getSynchronizationTemplate()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Description
     */
    public function getDescriptionTemplate()
    {
        return $this->getAmazonListingProduct()->getDescriptionTemplate();
    }

    //########################################

    /**
     * @param bool $asObjects
     * @return Ess_M2ePro_Model_Listing_Product_Variation_Option[]|array
     */
    public function getOptions($asObjects = false)
    {
        return $this->getParentObject()->getOptions($asObjects);
    }

    //########################################

    /**
     * @return string
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getSku()
    {
        $sku = '';

        // Options Models
        $options = $this->getOptions(true);

        // Configurable, Grouped product
        if ($this->getListingProduct()->getMagentoProduct()->isConfigurableType() ||
            $this->getListingProduct()->getMagentoProduct()->isGroupedType()) {
            foreach ($options as $option) {
                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
                $sku = $option->getChildObject()->getSku();
                break;
            }

        // Bundle product
        } else if ($this->getListingProduct()->getMagentoProduct()->isBundleType()) {
            foreach ($options as $option) {
                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */

                if (!$option->getProductId()) {
                    continue;
                }

                $sku != '' && $sku .= '-';
                $sku .= $option->getChildObject()->getSku();
            }

        // Simple with options product
        } else if ($this->getListingProduct()->getMagentoProduct()->isSimpleTypeWithCustomOptions()) {
            foreach ($options as $option) {
                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
                $sku != '' && $sku .= '-';
                $tempSku = $option->getChildObject()->getSku();
                if ($tempSku == '') {
                    $sku .= Mage::helper('M2ePro')->convertStringToSku($option->getOption());
                } else {
                    $sku .= $tempSku;
                }
            }

        // Downloadable with separated links product
        } else if ($this->getListingProduct()->getMagentoProduct()->isDownloadableTypeWithSeparatedLinks()) {

            /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */

            $option = reset($options);
            $sku = $option->getMagentoProduct()->getSku().'-'
                .Mage::helper('M2ePro')->convertStringToSku($option->getOption());
        }

        if (!empty($sku)) {
            return $this->applySkuModification($sku);
        }

        return $sku;
    }

    // ---------------------------------------

    protected function applySkuModification($sku)
    {
        if ($this->getAmazonListing()->isSkuModificationModeNone()) {
            return $sku;
        }

        $source = $this->getAmazonListing()->getSkuModificationSource();

        if ($this->getAmazonListing()->isSkuModificationModePrefix()) {
            $sku = $source['value'] . $sku;
        } elseif ($this->getAmazonListing()->isSkuModificationModePostfix()) {
            $sku = $sku . $source['value'];
        } elseif ($this->getAmazonListing()->isSkuModificationModeTemplate()) {
            $sku = str_replace('%value%', $sku, $source['value']);
        }

        return $sku;
    }

    //########################################

    public function getQty($magentoMode = false)
    {
        /** @var $calculator Ess_M2ePro_Model_Amazon_Listing_Product_QtyCalculator */
        $calculator = Mage::getModel('M2ePro/Amazon_Listing_Product_QtyCalculator');
        $calculator->setProduct($this->getListingProduct());
        $calculator->setIsMagentoMode($magentoMode);

        return $calculator->getVariationValue($this->getParentObject());
    }

    // ---------------------------------------

    public function getRegularPrice()
    {
        if (!$this->getAmazonListingProduct()->isAllowedForRegularCustomers()) {
            return null;
        }

        $src = $this->getAmazonSellingFormatTemplate()->getRegularPriceSource();

        /** @var $calculator Ess_M2ePro_Model_Amazon_Listing_Product_PriceCalculator */
        $calculator = Mage::getModel('M2ePro/Amazon_Listing_Product_PriceCalculator');
        $calculator->setSource($src)->setProduct($this->getListingProduct());
        $calculator->setCoefficient($this->getAmazonSellingFormatTemplate()->getRegularPriceCoefficient());
        $calculator->setVatPercent($this->getAmazonSellingFormatTemplate()->getRegularPriceVatPercent());
        $calculator->setPriceVariationMode($this->getAmazonSellingFormatTemplate()->getRegularPriceVariationMode());

        return $calculator->getVariationValue($this->getParentObject());
    }

    public function getRegularMapPrice()
    {
        if (!$this->getAmazonListingProduct()->isAllowedForRegularCustomers()) {
            return null;
        }

        $src = $this->getAmazonSellingFormatTemplate()->getRegularMapPriceSource();

        /** @var $calculator Ess_M2ePro_Model_Amazon_Listing_Product_PriceCalculator */
        $calculator = Mage::getModel('M2ePro/Amazon_Listing_Product_PriceCalculator');
        $calculator->setSource($src)->setProduct($this->getListingProduct());
        $calculator->setPriceVariationMode($this->getAmazonSellingFormatTemplate()->getRegularPriceVariationMode());

        return $calculator->getVariationValue($this->getParentObject());
    }

    public function getRegularSalePrice()
    {
        if (!$this->getAmazonListingProduct()->isAllowedForRegularCustomers()) {
            return null;
        }

        $src = $this->getAmazonSellingFormatTemplate()->getRegularSalePriceSource();

        /** @var $calculator Ess_M2ePro_Model_Amazon_Listing_Product_PriceCalculator */
        $calculator = Mage::getModel('M2ePro/Amazon_Listing_Product_PriceCalculator');
        $calculator->setSource($src)->setProduct($this->getListingProduct());
        $calculator->setIsSalePrice(true);
        $calculator->setCoefficient($this->getAmazonSellingFormatTemplate()->getRegularSalePriceCoefficient());
        $calculator->setVatPercent($this->getAmazonSellingFormatTemplate()->getRegularPriceVatPercent());
        $calculator->setPriceVariationMode($this->getAmazonSellingFormatTemplate()->getRegularPriceVariationMode());

        return $calculator->getVariationValue($this->getParentObject());
    }

    // ---------------------------------------

    /**
     * @return float
     */
    public function getRegularListPrice()
    {
        if (!$this->getAmazonListingProduct()->isAllowedForRegularCustomers()) {
            return 0.0;
        }

        $src = $this->getAmazonSellingFormatTemplate()->getListPriceSource();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_PriceCalculator $calculator  */
        $calculator = Mage::getModel('M2ePro/Amazon_Listing_Product_PriceCalculator');
        $calculator->setSource($src);
        $calculator->setProduct($this->getListingProduct());

        return (float)$calculator->getVariationValue($this->getParentObject());
    }

    // ---------------------------------------

    public function getBusinessPrice()
    {
        if (!$this->getAmazonListingProduct()->isAllowedForBusinessCustomers()) {
            return null;
        }

        $src = $this->getAmazonSellingFormatTemplate()->getBusinessPriceSource();

        /** @var $calculator Ess_M2ePro_Model_Amazon_Listing_Product_PriceCalculator */
        $calculator = Mage::getModel('M2ePro/Amazon_Listing_Product_PriceCalculator');
        $calculator->setSource($src)->setProduct($this->getListingProduct());
        $calculator->setCoefficient($this->getAmazonSellingFormatTemplate()->getBusinessPriceCoefficient());
        $calculator->setVatPercent($this->getAmazonSellingFormatTemplate()->getBusinessPriceVatPercent());
        $calculator->setPriceVariationMode($this->getAmazonSellingFormatTemplate()->getBusinessPriceVariationMode());

        return $calculator->getVariationValue($this->getParentObject());
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getBusinessDiscounts()
    {
        if (!$this->getAmazonListingProduct()->isAllowedForBusinessCustomers()) {
            return null;
        }

        if ($this->getAmazonSellingFormatTemplate()->isBusinessDiscountsModeNone()) {
            return array();
        }

        if ($this->getAmazonSellingFormatTemplate()->isBusinessDiscountsModeTier()) {
            $src = $this->getAmazonSellingFormatTemplate()->getBusinessDiscountsSource();

            $storeId = $this->getListing()->getStoreId();
            $src['tier_website_id'] = Mage::helper('M2ePro/Magento_Store')->getWebsite($storeId)->getId();

            /** @var $calculator Ess_M2ePro_Model_Amazon_Listing_Product_PriceCalculator */
            $calculator = Mage::getModel('M2ePro/Amazon_Listing_Product_PriceCalculator');
            $calculator->setSource($src)->setProduct($this->getListingProduct());
            $calculator->setSourceModeMapping(
                array(
                Ess_M2ePro_Model_Listing_Product_PriceCalculator::MODE_TIER
                    => Ess_M2ePro_Model_Amazon_Template_SellingFormat::BUSINESS_DISCOUNTS_MODE_TIER,
                )
            );
            $calculator->setCoefficient($this->getAmazonSellingFormatTemplate()->getBusinessDiscountsTierCoefficient());
            $calculator->setVatPercent($this->getAmazonSellingFormatTemplate()->getBusinessPriceVatPercent());
            $calculator->setPriceVariationMode(
                $this->getAmazonSellingFormatTemplate()->getBusinessPriceVariationMode()
            );

            return array_slice(
                $calculator->getVariationValue($this->getParentObject()), 0,
                Ess_M2ePro_Model_Amazon_Listing_Product::BUSINESS_DISCOUNTS_MAX_RULES_COUNT_ALLOWED,
                true
            );
        }

        /** @var Ess_M2ePro_Model_Amazon_Template_SellingFormat_BusinessDiscount[] $businessDiscounts */
        $businessDiscounts = $this->getAmazonSellingFormatTemplate()->getBusinessDiscounts(true);
        if (empty($businessDiscounts)) {
            return array();
        }

        $resultValue = array();

        foreach ($businessDiscounts as $businessDiscount) {
            /** @var $calculator Ess_M2ePro_Model_Amazon_Listing_Product_PriceCalculator */
            $calculator = Mage::getModel('M2ePro/Amazon_Listing_Product_PriceCalculator');
            $calculator->setSource($businessDiscount->getSource())->setProduct($this->getListingProduct());
            $calculator->setSourceModeMapping(
                array(
                Ess_M2ePro_Model_Listing_Product_PriceCalculator::MODE_PRODUCT
                    => Ess_M2ePro_Model_Amazon_Template_SellingFormat_BusinessDiscount::MODE_PRODUCT,
                Ess_M2ePro_Model_Listing_Product_PriceCalculator::MODE_SPECIAL
                    => Ess_M2ePro_Model_Amazon_Template_SellingFormat_BusinessDiscount::MODE_SPECIAL,
                Ess_M2ePro_Model_Listing_Product_PriceCalculator::MODE_ATTRIBUTE
                    => Ess_M2ePro_Model_Amazon_Template_SellingFormat_BusinessDiscount::MODE_ATTRIBUTE,
                )
            );
            $calculator->setCoefficient($businessDiscount->getCoefficient());
            $calculator->setVatPercent($this->getAmazonSellingFormatTemplate()->getBusinessPriceVatPercent());
            $calculator->setPriceVariationMode(
                $this->getAmazonSellingFormatTemplate()->getBusinessPriceVariationMode()
            );

            $resultValue[$businessDiscount->getQty()] = $calculator->getVariationValue($this->getParentObject());

            $rulesMaxCount = Ess_M2ePro_Model_Amazon_Listing_Product::BUSINESS_DISCOUNTS_MAX_RULES_COUNT_ALLOWED;

            if (count($resultValue) >= $rulesMaxCount) {
                break;
            }
        }

        return $resultValue;
    }

    //########################################
}
