<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Ebay_Template_Description_Source as DescriptionSource;

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Preview extends Mage_Adminhtml_Block_Abstract
{
    const NEXT = 0;
    const PREVIOUS = 1;
    const CURRENT = 3;

    /** @var Ess_M2ePro_Model_Ebay_Listing_Product $_ebayListingProduct */
    protected $_ebayListingProduct;

    //########################################

    public function __construct()
    {
        parent::__construct();

        $id = $this->getRequest()->getParam('currentProductId');

        $this->_ebayListingProduct = Mage::helper('M2ePro/Component_Ebay')->getObject(
            'Listing_Product', $id
        )->getChildObject();

        $this->setTemplate('M2ePro/ebay/listing/preview.phtml');
    }

    //########################################

    public function getProductShortInfo($direction)
    {
        $currentProductId = $this->getRequest()->getParam('currentProductId');
        $productIds = $this->getRequest()->getParam('productIds');

        $parsedProductIds = explode(',', $productIds);

        do {
            if ($currentProductId === current($parsedProductIds)) {
                break;
            }
        } while (next($parsedProductIds));

        if ($direction === self::NEXT && next($parsedProductIds) === false) {
            return null;
        }

        if ($direction === self::PREVIOUS && prev($parsedProductIds) === false) {
            return null;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $tempEbayListingProduct */

        $tempEbayListingProduct = Mage::helper('M2ePro/Component_Ebay')->getObject(
            'Listing_Product', current($parsedProductIds)
        )->getChildObject();

        return array(
            'title' => $tempEbayListingProduct->getMagentoProduct()->getName(),
            'id' => $tempEbayListingProduct->getMagentoProduct()->getProductId(),
            'url' => $this->getUrl(
                '*/adminhtml_ebay_listing/previewItems', array(
                'currentProductId' => current($parsedProductIds),
                'productIds' => $productIds,
                )
            )
        );
    }

    //########################################

    public function getTitle()
    {
        return Mage::helper('M2ePro')
            ->escapeHtml($this->_ebayListingProduct->getDescriptionTemplateSource()->getTitle());
    }

    public function getSubtitle()
    {
        return Mage::helper('M2ePro')
            ->escapeHtml($this->_ebayListingProduct->getDescriptionTemplateSource()->getSubTitle());
    }

    public function getDescription()
    {
        return $this->_ebayListingProduct->getDescriptionRenderer()->parseTemplate(
            $this->_ebayListingProduct->getDescriptionTemplateSource()->getDescription()
        );
    }

    public function getCondition()
    {
        return $this->getConditionHumanTitle(
            $this->_ebayListingProduct->getDescriptionTemplateSource()->getCondition()
        );
    }

    public function getConditionNote()
    {
        return Mage::helper('M2ePro')
            ->escapeHtml($this->_ebayListingProduct->getDescriptionTemplateSource()->getConditionNote());
    }

    // ---------------------------------------

    public function getPrice(array $variations)
    {
        $data = array(
            'price' => null,
            'price_stp' => null,
            'price_map' => null
        );

        if ($this->_ebayListingProduct->isListingTypeFixed()) {
            $data['price_fixed'] = number_format($this->_ebayListingProduct->getFixedPrice(), 2);

            if ($this->_ebayListingProduct->isPriceDiscountStp() &&
                $this->_ebayListingProduct->getPriceDiscountStp() > $this->_ebayListingProduct->getFixedPrice()) {
                $data['price_stp'] = number_format($this->_ebayListingProduct->getPriceDiscountStp(), 2);
            } elseif ($this->_ebayListingProduct->isPriceDiscountMap() &&
                      $this->_ebayListingProduct->getPriceDiscountMap() > $this->_ebayListingProduct->getFixedPrice()) {
                $data['price_map'] = number_format($this->_ebayListingProduct->getPriceDiscountMap(), 2);
            }
        } else {
            $data['price_start'] = number_format($this->_ebayListingProduct->getStartPrice(), 2);
        }

        $productPrice = null;

        if (empty($variations)) {
            $productPrice = isset($data['price_fixed']) ? $data['price_fixed'] : $data['price_start'];
        } else {
            $variationPrices = array();

            foreach ($variations['variations'] as $variation) {
                if ($variation['data']['qty']) {
                    $variationPrices[] = $variation['data'];
                }
            }

            if (!empty($variationPrices)) {
                $min = $variationPrices[0]['price'];
                $productPrice = $min;
                $data['price_stp'] = $variationPrices[0]['price_stp'];
                $data['price_map'] = $variationPrices[0]['price_map'];

                foreach ($variationPrices as $variationPrice) {
                    if ($variationPrice['price'] < $min) {
                        $productPrice = $variationPrice['price'];
                        $data['price_stp'] = $variationPrice['price_stp'];
                        $data['price_map'] = $variationPrice['price_map'];
                    }
                }
            }
        }

        $data['price'] = $productPrice;

        return $data;
    }

    public function getQty()
    {
        return $this->_ebayListingProduct->getQty();
    }

    public function getCurrency()
    {
        return $this->_ebayListingProduct->getEbayMarketplace()->getCurrency();
    }

    public function getCurrencySymbol()
    {
        return Mage::app()->getLocale()->currency($this->getCurrency())->getSymbol();
    }

    // ---------------------------------------

    public function getVariations()
    {
        $variations = $this->_ebayListingProduct->getVariations(true);
        $data = array();

        if ($this->_ebayListingProduct->getEbaySellingFormatTemplate()->isIgnoreVariationsEnabled()) {
            return array();
        }

        if (!$this->_ebayListingProduct->isListingTypeFixed()) {
            return array();
        }

        if (!$this->_ebayListingProduct->getEbayMarketplace()->isMultivariationEnabled()) {
            return array();
        }

        foreach ($variations as $variation) {

            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            /** @var $productVariation Ess_M2ePro_Model_Ebay_Listing_Product_Variation */

            $productVariation = $variation->getChildObject();

            $variationQty = $productVariation->getQty();
            if ($variationQty == 0) {
                continue;
            }

            /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */

            $options = $productVariation->getOptions(true);

            $variationData = array(
                'price' => number_format($productVariation->getPrice(), 2),
                'qty' => $variationQty,
                'price_stp' => null,
                'price_map' => null
            );

            if ($this->_ebayListingProduct->isPriceDiscountStp()
                && $productVariation->getPriceDiscountStp() > $productVariation->getPrice()) {
                $variationData['price_stp'] = number_format($productVariation->getPriceDiscountStp(), 2);
            } elseif ($this->_ebayListingProduct->isPriceDiscountMap()
                && $productVariation->getPriceDiscountMap() > $productVariation->getPrice()) {
                $variationData['price_map'] = number_format($productVariation->getPriceDiscountMap(), 2);
            }

            $variationSpecifics = array();

            foreach ($options as $option) {
                $optionTitle = trim($option->getOption());
                $attributeTitle = trim($option->getAttribute());

                $variationSpecifics[$attributeTitle] = $optionTitle;
                $data['variation_sets'][$attributeTitle][] = $optionTitle;
            }

            $variationData = array(
                'data' => $variationData,
                'specifics' => $variationSpecifics
            );

            $data['variations'][] = $variationData;
        }

        if (!empty($data['variation_sets'])) {
            foreach ($data['variation_sets'] as &$variationSets) {
                $variationSets = array_unique($variationSets);
            }
        }

        return $data;
    }

    // ---------------------------------------

    protected function getConfigurableImagesAttributeLabels()
    {
        $descriptionTemplate = $this->_ebayListingProduct->getEbayDescriptionTemplate();

        if (!$descriptionTemplate->isVariationConfigurableImages()) {
            return array();
        }

        $product = $this->_ebayListingProduct->getMagentoProduct()->getProduct();

        $attributeCodes = $descriptionTemplate->getDecodedVariationConfigurableImages();
        $attributes = array();

        foreach ($attributeCodes as $attributeCode) {
            /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            $attribute = $product->getResource()->getAttribute($attributeCode);

            if (!$attribute) {
                continue;
            }

            $attribute->setStoreId($product->getStoreId());
            $attributes[] = $attribute;
        }

        if (empty($attributes)) {
            return array();
        }

        $attributeLabels = array();

        /** @var $productTypeInstance Mage_Catalog_Model_Product_Type_Configurable */
        $productTypeInstance = $this->_ebayListingProduct->getMagentoProduct()->getTypeInstance();

        foreach ($productTypeInstance->getConfigurableAttributes() as $configurableAttribute) {

            /** @var $configurableAttribute Mage_Catalog_Model_Product_Type_Configurable_Attribute */
            $configurableAttribute->setStoteId($product->getStoreId());

            foreach ($attributes as $attribute) {
                if ((int)$attribute->getAttributeId() == (int)$configurableAttribute->getAttributeId()) {
                    $attributeLabels = array();
                    foreach ($attribute->getStoreLabels() as $storeLabel) {
                        $attributeLabels[] = trim($storeLabel);
                    }

                    $attributeLabels[] = trim($configurableAttribute->getData('label'));
                    $attributeLabels[] = trim($attribute->getFrontendLabel());

                    $attributeLabels = array_filter($attributeLabels);

                    break 2;
                }
            }
        }

        return $attributeLabels;
    }

    protected function getImagesDataByAttributeLabels(array $attributeLabels)
    {
        $images = array();
        $imagesLinks = array();
        $attributeLabel = false;

        foreach ($this->_ebayListingProduct->getVariations(true) as $variation) {

            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */

            if ($variation->getChildObject()->isDelete() || !$variation->getChildObject()->getQty()) {
                continue;
            }

            foreach ($variation->getOptions(true) as $option) {

                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */

                $optionLabel = trim($option->getAttribute());
                $optionValue = trim($option->getOption());

                $foundAttributeLabel = false;
                foreach ($attributeLabels as $tempLabel) {
                    if (strtolower($tempLabel) == strtolower($optionLabel)) {
                        $foundAttributeLabel = $optionLabel;
                        break;
                    }
                }

                if ($foundAttributeLabel === false) {
                    continue;
                }

                if (!isset($imagesLinks[$optionValue])) {
                    $imagesLinks[$optionValue] = array();
                }

                $attributeLabel = $foundAttributeLabel;
                $optionImages = $this->_ebayListingProduct->getEbayDescriptionTemplate()
                                                          ->getSource($option->getMagentoProduct())
                                                          ->getVariationImages();

                foreach ($optionImages as $image) {
                    if (!$image->getUrl()) {
                        continue;
                    }

                    if (count($imagesLinks[$optionValue]) >= DescriptionSource::VARIATION_IMAGES_COUNT_MAX) {
                        break 2;
                    }

                    if (!isset($images[$image->getHash()])) {
                        $imagesLinks[$optionValue][] = $image->getUrl();
                        $images[$image->getHash()] = $image;
                    }
                }
            }
        }

        if (!$attributeLabel || !$imagesLinks) {
            return array();
        }

        return array(
            'specific' => $attributeLabel,
            'images'   => $imagesLinks
        );
    }

    public function getImages()
    {
        $images = array();

        if ($this->_ebayListingProduct->isVariationsReady()) {
            $attributeLabels = array();
            $images['variations'] = array();

            if ($this->_ebayListingProduct->getMagentoProduct()->isConfigurableType()) {
                $attributeLabels = $this->getConfigurableImagesAttributeLabels();
            }

            if ($this->_ebayListingProduct->getMagentoProduct()->isGroupedType()) {
                $attributeLabels = array(Ess_M2ePro_Model_Magento_Product_Variation::GROUPED_PRODUCT_ATTRIBUTE_LABEL);
            }

            if (!empty($attributeLabels)) {
                $images['variations'] = $this->getImagesDataByAttributeLabels($attributeLabels);
            }
        }

        $links = array();
        foreach ($this->_ebayListingProduct->getDescriptionTemplateSource()->getGalleryImages() as $image) {
            if (!$image->getUrl()) {
                continue;
            }

            $links[] = $image->getUrl();
        }

        $images['gallery'] = $links;
        return $images;
    }

    // ---------------------------------------

    public function getCategory()
    {
        $finalCategory = '';
        $marketplaceId = $this->_ebayListingProduct->getMarketplace()->getMarketplaceId();

        if ($this->_ebayListingProduct->getCategoryTemplateSource() === null) {
            return $finalCategory;
        }

        $categoryId = $this->_ebayListingProduct->getCategoryTemplateSource()->getMainCategory();
        $categoryTitle = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getPath($categoryId, $marketplaceId);

        if (!$categoryTitle) {
            return $categoryTitle;
        }

        $finalCategory = '<a>' . str_replace('>', '</a> > <a>', $categoryTitle) . '</a> (' . $categoryId . ')';

        return $finalCategory;
    }

    public function getOtherCategories()
    {
        $otherCategoriesFinalTitles = array();

        $marketplaceId = $this->_ebayListingProduct->getMarketplace()->getMarketplaceId();
        $accountId = $this->_ebayListingProduct->getEbayAccount()->getId();

        $otherCategoryTemplateSource = $this->_ebayListingProduct->getOtherCategoryTemplateSource();

        if ($otherCategoryTemplateSource === null) {
            return $otherCategoriesFinalTitles;
        }

        $otherCategoriesIds = array(
            'secondary' => $otherCategoryTemplateSource->getSecondaryCategory(),
            'primary_store' => $otherCategoryTemplateSource->getStoreCategoryMain(),
            'secondary_store' => $otherCategoryTemplateSource->getStoreCategorySecondary()
        );

        $otherCategoriesTitles = array(
            'secondary' => Mage::helper('M2ePro/Component_Ebay_Category_Ebay')
                ->getPath($otherCategoriesIds['secondary'], $marketplaceId),
            'primary_store' => Mage::helper('M2ePro/Component_Ebay_Category_Store')
                ->getPath($otherCategoriesIds['primary_store'], $accountId),
            'secondary_store' => Mage::helper('M2ePro/Component_Ebay_Category_Store')
                ->getPath($otherCategoriesIds['secondary_store'], $accountId)
        );

        foreach ($otherCategoriesTitles as $otherCategoryType => $otherCategoryTitle) {
            if ($otherCategoryTitle) {
                $otherCategoriesFinalTitles[$otherCategoryType] =
                    '<a>' . str_replace('>', '</a> > <a>', $otherCategoryTitle)
                    . '</a> (' . $otherCategoriesIds[$otherCategoryType] . ')';
            }
        }

        return $otherCategoriesFinalTitles;
    }

    public function getSpecifics()
    {
        $data = array();

        if ($this->_ebayListingProduct->getCategoryTemplate() === null) {
            return $data;
        }

        foreach ($this->_ebayListingProduct->getCategoryTemplate()->getSpecifics(true) as $specific) {

            /** @var $specific Ess_M2ePro_Model_Ebay_Template_Category_Specific */

            $tempAttributeLabel = $specific->getSource($this->_ebayListingProduct->getMagentoProduct())
                ->getLabel();
            $tempAttributeValues = $specific->getSource($this->_ebayListingProduct->getMagentoProduct())
                ->getValues();

            $values = array();
            foreach ($tempAttributeValues as $tempAttributeValue) {
                if ($tempAttributeValue == '--') {
                    continue;
                }

                $values[] = $tempAttributeValue;
            }

            if (empty($values)) {
                continue;
            }

            $data[] = array(
                'name' => $tempAttributeLabel,
                'value' => $values
            );
        }

        return $data;
    }

    //########################################

    protected function getConditionHumanTitle($code)
    {
        $codes = array(
            Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_NEW =>
                Mage::helper('M2ePro')->__('New'),
            Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_NEW_OTHER =>
                Mage::helper('M2ePro')->__('New Other'),
            Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_NEW_WITH_DEFECT =>
                Mage::helper('M2ePro')->__('New With Defects'),
            Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_MANUFACTURER_REFURBISHED =>
                Mage::helper('M2ePro')->__('Manufacturer Refurbished'),
            Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_SELLER_REFURBISHED =>
                Mage::helper('M2ePro')->__('Seller Refurbished, Re-manufactured'),
            Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_USED =>
                Mage::helper('M2ePro')->__('Used'),
            Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_VERY_GOOD =>
                Mage::helper('M2ePro')->__('Very Good'),
            Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_GOOD =>
                Mage::helper('M2ePro')->__('Good'),
            Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_ACCEPTABLE =>
                Mage::helper('M2ePro')->__('Acceptable'),
            Ess_M2ePro_Model_Ebay_Template_Description::CONDITION_EBAY_NOT_WORKING =>
                Mage::helper('M2ePro')->__('For Parts or Not Working')
        );

        if (!isset($codes[$code])) {
            return '';
        }

        return $codes[$code];
    }

    protected function getCountryHumanTitle($countryId)
    {
        $countries = Mage::helper('M2ePro/Magento')->getCountries();

        foreach ($countries as $country) {
            if ($countryId === $country['country_id']) {
                return Mage::helper('M2ePro')->__($country['name']);
            }
        }

        return '';
    }

    protected function getShippingServiceHumanTitle($serviceMethodId)
    {
        $shippingServicesInfo = $this->_ebayListingProduct->getEbayMarketplace()->getShippingInfo();

        foreach ($shippingServicesInfo as $shippingServiceInfo) {
            foreach ($shippingServiceInfo['methods'] as $shippingServiceMethod) {
                if ($serviceMethodId == $shippingServiceMethod['ebay_id']) {
                    return Mage::helper('M2ePro')->__($shippingServiceMethod['title']);
                }
            }
        }

        return '';
    }

    protected function getShippingLocationHumanTitle(array $locationIds)
    {
        $locationsTitle = array();
        $locationsInfo = $this->_ebayListingProduct->getEbayMarketplace()->getShippingLocationInfo();

        foreach ($locationIds as $locationId) {
            foreach ($locationsInfo as $locationInfo) {
                if ($locationId == $locationInfo['ebay_id']) {
                    $locationsTitle[] = Mage::helper('M2ePro')->__($locationInfo['title']);
                }
            }
        }

        return $locationsTitle;
    }

    protected function getShippingExcludeLocationHumanTitle($excludeLocationId)
    {
        $excludeLocationsInfo = $this->_ebayListingProduct->getEbayMarketplace()->getShippingLocationExcludeInfo();

        foreach ($excludeLocationsInfo as $excludeLocationInfo) {
            if ($excludeLocationId == $excludeLocationInfo['ebay_id']) {
                return Mage::helper('M2ePro')->__($excludeLocationInfo['title']);
            }
        }

        return '';
    }

    public function getItemLocation()
    {
        $itemLocation = array(
            $this->_ebayListingProduct->getShippingTemplateSource()->getPostalCode(),
            $this->_ebayListingProduct->getShippingTemplateSource()->getAddress(),
            $this->getCountryHumanTitle($this->_ebayListingProduct->getShippingTemplateSource()->getCountry())
        );
        return implode(', ', $itemLocation);
    }

    public function getShippingDispatchTime()
    {
        $dispatchTime = null;

        if ($this->_ebayListingProduct->getShippingTemplate()->isLocalShippingFlatEnabled() ||
            $this->_ebayListingProduct->getShippingTemplate()->isLocalShippingCalculatedEnabled()
        ) {
            $dispatchTimeId = $this->_ebayListingProduct->getShippingTemplateSource()->getDispatchTime();

            if ($dispatchTimeId == 0) {
                return Mage::helper('M2ePro')->__('Same Business Day');
            } else {
                $dispatchInfo = $this->_ebayListingProduct->getEbayMarketplace()->getDispatchInfo();

                foreach ($dispatchInfo as $dispatch) {
                    if ($dispatch['ebay_id'] == $dispatchTimeId) {
                        $dispatchTime = $dispatch['title'];
                        break;
                    }
                }

                return Mage::helper('M2ePro')->__($dispatchTime);
            }
        }

        return $dispatchTime;
    }

    public function getShippingLocalHandlingCost()
    {
        if ($this->_ebayListingProduct->getShippingTemplate()->isLocalShippingCalculatedEnabled()) {
            return $this->_ebayListingProduct->getShippingTemplate()->getCalculatedShipping()
                                             ->getLocalHandlingCost();
        }

        return 0;
    }

    public function getShippingInternationalHandlingCost()
    {
        if ($this->_ebayListingProduct->getShippingTemplate()->isLocalShippingCalculatedEnabled()) {
            return $this->_ebayListingProduct->getShippingTemplate()->getCalculatedShipping()
                                             ->getInternationalHandlingCost();
        }

        return 0;
    }

    public function getShippingLocalType()
    {
        if ($this->_ebayListingProduct->getShippingTemplate()->isLocalShippingLocalEnabled()) {
            return Mage::helper('M2ePro')->__('No Shipping - local pickup only');
        }

        if ($this->_ebayListingProduct->getShippingTemplate()->isLocalShippingFreightEnabled()) {
            return Mage::helper('M2ePro')->__('Freight - large Items');
        }

        if ($this->_ebayListingProduct->getShippingTemplate()->isLocalShippingFlatEnabled()) {
            return Mage::helper('M2ePro')->__('Flat - same cost to all Buyers');
        }

        if ($this->_ebayListingProduct->getShippingTemplate()->isLocalShippingCalculatedEnabled()) {
            return Mage::helper('M2ePro')->__('Calculated - cost varies by Buyer Location');
        }
    }

    public function getShippingInternationalType()
    {
        if ($this->_ebayListingProduct->getShippingTemplate()->isInternationalShippingFlatEnabled()) {
            return Mage::helper('M2ePro')->__('Flat - same cost to all Buyers');
        }

        if ($this->_ebayListingProduct->getShippingTemplate()->isInternationalShippingCalculatedEnabled()) {
            return Mage::helper('M2ePro')->__('Calculated - cost varies by Buyer Location');
        }
    }

    public function isLocalShippingCalculated()
    {
        return $this->_ebayListingProduct->getShippingTemplate()->isLocalShippingCalculatedEnabled();
    }

    public function isInternationalShippingCalculated()
    {
        return $this->_ebayListingProduct->getShippingTemplate()->isInternationalShippingCalculatedEnabled();
    }

    public function getShippingLocalServices()
    {
        $services = array();
        $storeId = $this->_ebayListingProduct->getListing()->getStoreId();

        foreach ($this->_ebayListingProduct->getShippingTemplate()->getServices(true) as $service) {

            /** @var $service Ess_M2ePro_Model_Ebay_Template_Shipping_Service */

            if (!$service->isShippingTypeLocal()) {
                continue;
            }

            $tempDataMethod = array(
                'service' => $this->getShippingServiceHumanTitle($service->getShippingValue())
            );

            if ($this->_ebayListingProduct->getShippingTemplate()->isLocalShippingFlatEnabled()) {
                $tempDataMethod['cost'] = $service->getSource($this->_ebayListingProduct->getMagentoProduct())
                    ->getCost($storeId);

                $tempDataMethod['cost_additional'] = $service->getSource(
                    $this->_ebayListingProduct->getMagentoProduct()
                )->getCostAdditional($storeId);
            }

            if ($this->_ebayListingProduct->getShippingTemplate()->isLocalShippingCalculatedEnabled()) {
                $tempDataMethod['is_free'] = $service->isCostModeFree();
            }

            $services[] = $tempDataMethod;
        }

        return $services;
    }

    public function getShippingInternationalServices()
    {
        $services = array();
        $storeId = $this->_ebayListingProduct->getListing()->getStoreId();

        foreach ($this->_ebayListingProduct->getShippingTemplate()->getServices(true) as $service) {

            /** @var $service Ess_M2ePro_Model_Ebay_Template_Shipping_Service */

            if (!$service->isShippingTypeInternational()) {
                continue;
            }

            $tempDataMethod = array(
                'service' => $this->getShippingServiceHumanTitle($service->getShippingValue()),
                'locations' => implode(', ', $this->getShippingLocationHumanTitle($service->getLocations()))
            );

            if ($this->_ebayListingProduct->getShippingTemplate()->isInternationalShippingFlatEnabled()) {
                $tempDataMethod['cost'] = $service->getSource($this->_ebayListingProduct->getMagentoProduct())
                    ->getCost($storeId);

                $tempDataMethod['cost_additional'] = $service->getSource(
                    $this->_ebayListingProduct->getMagentoProduct()
                )->getCostAdditional($storeId);
            }

            $services[] = $tempDataMethod;
        }

        return $services;
    }

    public function getPayment()
    {
        $data = array();

        if ($this->_ebayListingProduct->getPaymentTemplate()->isPayPalEnabled()) {
            $data['paypal'] = true;
        }

        $services = $this->_ebayListingProduct->getPaymentTemplate()->getServices(true);
        $paymentMethodsInfo = $this->_ebayListingProduct->getMarketplace()->getChildObject()->getPaymentInfo();

        $paymentMethods = array();
        foreach ($services as $service) {
            /** @var $service Ess_M2ePro_Model_Ebay_Template_Payment_Service */

            foreach ($paymentMethodsInfo as $paymentMethodInfo) {
                if ($service->getCodeName() == $paymentMethodInfo['ebay_id']) {
                    $paymentMethods[] = $paymentMethodInfo['title'];
                }
            }
        }

        $data['paymentMethods'] = $paymentMethods;

        return $data;
    }

    public function getShippingExcludedLocations()
    {
        $locations = array();

        foreach ($this->_ebayListingProduct->getShippingTemplate()->getExcludedLocations() as $location) {
            $locations[] = $this->getShippingExcludeLocationHumanTitle($location['code']);
        }

        return implode(', ', $locations);
    }

    public function getShippingInternationalGlobalOffer()
    {
        return $this->_ebayListingProduct->getShippingTemplate()->isGlobalShippingProgramEnabled();
    }

    // ---------------------------------------

    public function getReturnPolicy()
    {
        $helper = Mage::helper('M2ePro');
        $returnPolicyTitles = array(
            'returns_accepted'      => '',
            'returns_within'        => '',
            'refund'                => '',
            'shipping_cost_paid_by' => '',

            'international_returns_accepted'      => '',
            'international_returns_within'        => '',
            'international_refund'                => '',
            'international_shipping_cost_paid_by' => '',

            'description' => ''
        );

        $returnAccepted = $this->_ebayListingProduct->getReturnTemplate()->getAccepted();
        foreach ($this->getDictionaryInfo('returns_accepted') as $returnAcceptedId) {
            if ($returnAccepted === $returnAcceptedId['ebay_id']) {
                $returnPolicyTitles['returns_accepted'] = $helper->__($returnAcceptedId['title']);
                break;
            }
        }

        $returnWithin = $this->_ebayListingProduct->getReturnTemplate()->getWithin();
        foreach ($this->getDictionaryInfo('returns_within') as $returnWithinId) {
            if ($returnWithin === $returnWithinId['ebay_id']) {
                $returnPolicyTitles['returns_within'] = $helper->__($returnWithinId['title']);
                break;
            }
        }

        $returnRefund = $this->_ebayListingProduct->getReturnTemplate()->getOption();
        foreach ($this->getDictionaryInfo('refund') as $returnRefundId) {
            if ($returnRefund === $returnRefundId['ebay_id']) {
                $returnPolicyTitles['refund'] = $helper->__($returnRefundId['title']);
                break;
            }
        }

        $returnShippingCost = $this->_ebayListingProduct->getReturnTemplate()->getShippingCost();
        foreach ($this->getDictionaryInfo('shipping_cost_paid_by') as $returnShippingCostId) {
            if ($returnShippingCost === $returnShippingCostId['ebay_id']) {
                $returnPolicyTitles['shipping_cost_paid_by'] = $helper->__($returnShippingCostId['title']);
                break;
            }
        }

        // ---------------------------------------

        $returnAccepted = $this->_ebayListingProduct->getReturnTemplate()->getInternationalAccepted();
        foreach ($this->getInternationalDictionaryInfo('returns_accepted') as $returnAcceptedId) {
            if ($returnAccepted === $returnAcceptedId['ebay_id']) {
                $returnPolicyTitles['international_returns_accepted'] = $helper->__($returnAcceptedId['title']);
                break;
            }
        }

        $returnWithin = $this->_ebayListingProduct->getReturnTemplate()->getInternationalWithin();
        foreach ($this->getInternationalDictionaryInfo('returns_within') as $returnWithinId) {
            if ($returnWithin === $returnWithinId['ebay_id']) {
                $returnPolicyTitles['international_returns_within'] = $helper->__($returnWithinId['title']);
                break;
            }
        }

        $returnRefund = $this->_ebayListingProduct->getReturnTemplate()->getInternationalOption();
        foreach ($this->getInternationalDictionaryInfo('refund') as $returnRefundId) {
            if ($returnRefund === $returnRefundId['ebay_id']) {
                $returnPolicyTitles['international_refund'] = $helper->__($returnRefundId['title']);
                break;
            }
        }

        $returnShippingCost = $this->_ebayListingProduct->getReturnTemplate()->getInternationalShippingCost();
        foreach ($this->getInternationalDictionaryInfo('shipping_cost_paid_by') as $shippingCostId) {
            if ($returnShippingCost === $shippingCostId['ebay_id']) {
                $returnPolicyTitles['international_shipping_cost_paid_by'] = $helper->__($shippingCostId['title']);
                break;
            }
        }

        // ---------------------------------------

        $returnPolicyTitles['description'] = $this->_ebayListingProduct->getReturnTemplate()->getDescription();

        return $returnPolicyTitles;
    }

    public function isDomesticReturnsAccepted()
    {
        $template = $this->_ebayListingProduct->getReturnTemplate();
        return $template->getAccepted() === Ess_M2ePro_Model_Ebay_Template_ReturnPolicy::RETURNS_ACCEPTED;
    }

    public function isInternationalReturnsAccepted()
    {
        $template = $this->_ebayListingProduct->getReturnTemplate();

        return $this->isDomesticReturnsAccepted() &&
               $template->getInternationalAccepted() === Ess_M2ePro_Model_Ebay_Template_ReturnPolicy::RETURNS_ACCEPTED;
    }

    //########################################

    protected function getDictionaryInfo($key)
    {
        $returnPolicyInfo = $this->_ebayListingProduct->getEbayMarketplace()->getReturnPolicyInfo();
        return !empty($returnPolicyInfo[$key]) ? $returnPolicyInfo[$key] : array();
    }

    protected function getInternationalDictionaryInfo($key)
    {
        $returnPolicyInfo = $this->_ebayListingProduct->getEbayMarketplace()->getReturnPolicyInfo();

        if (!empty($returnPolicyInfo['international_'.$key])) {
            return $returnPolicyInfo['international_'.$key];
        }

        return $this->getDictionaryInfo($key);
    }

    //########################################
}
