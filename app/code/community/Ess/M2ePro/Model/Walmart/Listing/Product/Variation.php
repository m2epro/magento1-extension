<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Listing_Product_PriceCalculator as PriceCalculator;
use Ess_M2ePro_Model_Walmart_Template_SellingFormat_Promotion as Promotion;

/**
 * @method Ess_M2ePro_Model_Listing_Product_Variation getParentObject()
 */
class Ess_M2ePro_Model_Walmart_Listing_Product_Variation extends Ess_M2ePro_Model_Component_Child_Walmart_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Walmart_Listing_Product_Variation');
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
     * @return Ess_M2ePro_Model_Walmart_Account
     */
    public function getWalmartAccount()
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
     * @return Ess_M2ePro_Model_Walmart_Marketplace
     */
    public function getWalmartMarketplace()
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
     * @return Ess_M2ePro_Model_Walmart_Listing
     */
    public function getWalmartListing()
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
     * @return Ess_M2ePro_Model_Walmart_Listing_Product
     */
    public function getWalmartListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->getWalmartListingProduct()->getSellingFormatTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Template_SellingFormat
     */
    public function getWalmartSellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        return $this->getWalmartListingProduct()->getSynchronizationTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Template_Synchronization
     */
    public function getWalmartSynchronizationTemplate()
    {
        return $this->getSynchronizationTemplate()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Description
     */
    public function getDescriptionTemplate()
    {
        return $this->getWalmartListingProduct()->getDescriptionTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Template_Description
     */
    public function getWalmartDescriptionTemplate()
    {
        return $this->getDescriptionTemplate()->getChildObject();
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

        return $sku;
    }

    //########################################

    public function getQty($magentoMode = false)
    {
        /** @var $calculator Ess_M2ePro_Model_Walmart_Listing_Product_QtyCalculator */
        $calculator = Mage::getModel('M2ePro/Walmart_Listing_Product_QtyCalculator');
        $calculator->setProduct($this->getListingProduct());
        $calculator->setIsMagentoMode($magentoMode);

        return $calculator->getVariationValue($this->getParentObject());
    }

    // ---------------------------------------

    public function getPrice()
    {
        $src = $this->getWalmartSellingFormatTemplate()->getPriceSource();

        /** @var $calculator Ess_M2ePro_Model_Walmart_Listing_Product_PriceCalculator */
        $calculator = Mage::getModel('M2ePro/Walmart_Listing_Product_PriceCalculator');
        $calculator->setSource($src)->setProduct($this->getListingProduct());
        $calculator->setCoefficient($this->getWalmartSellingFormatTemplate()->getPriceCoefficient());
        $calculator->setVatPercent($this->getWalmartSellingFormatTemplate()->getPriceVatPercent());
        $calculator->setPriceVariationMode($this->getWalmartSellingFormatTemplate()->getPriceVariationMode());

        return $calculator->getVariationValue($this->getParentObject());
    }

    // ---------------------------------------

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getPromotions()
    {
        if ($this->getWalmartSellingFormatTemplate()->isPromotionsModeNo()) {
            return array();
        }

        /** @var Ess_M2ePro_Model_Walmart_Template_SellingFormat_Promotion[] $promotions */
        $promotions = $this->getWalmartSellingFormatTemplate()->getPromotions(true);
        if (empty($promotions)) {
            return array();
        }

        $resultPromotions = array();

        foreach ($promotions as $promotion) {

            /** @var $priceCalculator Ess_M2ePro_Model_Walmart_Listing_Product_PriceCalculator */
            $priceCalculator = Mage::getModel('M2ePro/Walmart_Listing_Product_PriceCalculator');
            $priceCalculator->setSource($promotion->getPriceSource())->setProduct($this->getListingProduct());
            $priceCalculator->setSourceModeMapping(
                array(
                PriceCalculator::MODE_PRODUCT   => Promotion::PRICE_MODE_PRODUCT,
                PriceCalculator::MODE_SPECIAL   => Promotion::PRICE_MODE_SPECIAL,
                PriceCalculator::MODE_ATTRIBUTE => Promotion::PRICE_MODE_ATTRIBUTE,
                )
            );
            $priceCalculator->setCoefficient($promotion->getPriceCoefficient());
            $priceCalculator->setVatPercent($this->getWalmartSellingFormatTemplate()->getPriceVatPercent());
            $priceCalculator->setPriceVariationMode(
                $this->getWalmartSellingFormatTemplate()->getPriceVariationMode()
            );

            /** @var $comparisonPriceCalculator Ess_M2ePro_Model_Walmart_Listing_Product_PriceCalculator */
            $comparisonPriceCalculator = Mage::getModel('M2ePro/Walmart_Listing_Product_PriceCalculator');
            $comparisonPriceCalculator->setSource(
                $promotion->getComparisonPriceSource()
            )->setProduct(
                $this->getListingProduct()
            );
            $comparisonPriceCalculator->setSourceModeMapping(
                array(
                PriceCalculator::MODE_PRODUCT   => Promotion::COMPARISON_PRICE_MODE_PRODUCT,
                PriceCalculator::MODE_SPECIAL   => Promotion::COMPARISON_PRICE_MODE_SPECIAL,
                PriceCalculator::MODE_ATTRIBUTE => Promotion::COMPARISON_PRICE_MODE_ATTRIBUTE,
                )
            );
            $comparisonPriceCalculator->setCoefficient($promotion->getComparisonPriceCoefficient());
            $comparisonPriceCalculator->setVatPercent($this->getWalmartSellingFormatTemplate()->getPriceVatPercent());
            $comparisonPriceCalculator->setPriceVariationMode(
                $this->getWalmartSellingFormatTemplate()->getPriceVariationMode()
            );

            $promotionSource = $promotion->getSource($this->getWalmartListingProduct()->getActualMagentoProduct());

            $resultPromotions[] = array(
                'start_date'       => $promotionSource->getStartDate(),
                'end_date'         => $promotionSource->getEndDate(),
                'price'            => $priceCalculator->getVariationValue($this->getParentObject()),
                'comparison_price' => $comparisonPriceCalculator->getVariationValue($this->getParentObject()),
                'type'             => strtoupper($promotion->getType())
            );

            if (count($resultPromotions) >= Ess_M2ePro_Model_Walmart_Listing_Product::PROMOTIONS_MAX_ALLOWED_COUNT) {
                break;
            }
        }

        return $resultPromotions;
    }

    //########################################
}
