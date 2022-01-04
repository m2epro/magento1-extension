<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Variations
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Abstract
{
    protected $_variationsThatCanNotBeDeleted = array();

    //########################################

    public function getData()
    {
        $data = array(
            'is_variation_item' => $this->_isVariationItem
        );

        $this->logLimitationsAndReasons();

        if (!$this->_isVariationItem) {
            return $data;
        }

        $data['variation'] = $this->getVariationsData();

        if ($sets = $this->getSetsData()) {
            $data['variations_sets'] = $sets;
        }

        if ($variationsThatCanNotBeDeleted = $this->getVariationsThatCanNotBeDeleted()) {
            $data['variations_that_can_not_be_deleted'] = $variationsThatCanNotBeDeleted;
        }

        return $data;
    }

    //########################################

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    protected function getVariationsData()
    {
        $data = array();

        $qtyMode = $this->getEbayListingProduct()->getEbaySellingFormatTemplate()->getQtyMode();

        $productsIds = array();
        $variationMetaData = array();

        foreach ($this->getListingProduct()->getVariations(true) as $variation) {
            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            /** @var $ebayVariation Ess_M2ePro_Model_Ebay_Listing_Product_Variation */

            $ebayVariation = $variation->getChildObject();

            $item = array(
                '_instance_' => $variation,
                'qty'        => $ebayVariation->isDelete() ? 0 : $ebayVariation->getQty(),
                'sku'        => $this->getSku($variation),
                'add'        => $ebayVariation->isAdd(),
                'delete'     => $ebayVariation->isDelete(),
                'specifics'  => array()
            );
            if ($ebayVariation->isDelete()) {
                $item['sku'] = 'del-' . sha1(microtime(1).$ebayVariation->getOnlineSku());
            }

            $item = array_merge($item, $this->getVariationPriceData($variation));

            if (($qtyMode == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED ||
                $qtyMode == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT) && !$item['delete']) {
                foreach ($variation->getOptions(true) as $option) {
                    $productsIds[] = $option->getProductId();
                }
            }

            $variationDetails = $this->getVariationDetails($variation);

            if (!empty($variationDetails)) {
                $item['details'] = $variationDetails;
            }

            foreach ($variation->getOptions(true) as $option) {
                /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */

                $item['specifics'][$option->getAttribute()] = $option->getOption();
            }

            //-- MPN Specific has been changed
            if (!empty($item['details']['mpn_previous']) && !empty($item['details']['mpn']) &&
                $item['details']['mpn_previous'] != $item['details']['mpn']) {
                $oneMoreVariation = array(
                    'qty'       => 0,
                    'price'     => $item['price'],
                    'sku'       => 'del-' . sha1(microtime(1) . $item['sku']),
                    'add'       => 0,
                    'delete'    => 1,
                    'specifics' => $item['specifics'],
                    'has_sales' => true,
                    'details'   => $item['details']
                );
                $oneMoreVariation['details']['mpn'] = $item['details']['mpn_previous'];

                $specificsReplacements = $this->getEbayListingProduct()->getVariationSpecificsReplacements();
                if (!empty($specificsReplacements)) {
                    $oneMoreVariation['variations_specifics_replacements'] = $specificsReplacements;
                }

                unset($item['details']['mpn_previous']);

                $this->_variationsThatCanNotBeDeleted[] = $oneMoreVariation;
            }

            if (isset($item['price']) && $variation->getChildObject()->getOnlinePrice() == $item['price']) {
                $item['price_not_changed'] = true;
            }

            if (isset($item['qty']) && $variation->getChildObject()->getOnlineQty() == $item['qty']) {
                $item['qty_not_changed'] = true;
            }

            $data[] = $item;
            $variationMetaData[$variation->getId()] = array(
                // @codingStandardsIgnoreLine
                'index'        => count($data) - 1,
                'online_qty'   => $variation->getChildObject()->getOnlineQty(),
                'online_price' => $variation->getChildObject()->getOnlinePrice()
            );
        }

        $this->addMetaData('variation_data', $variationMetaData);

        $this->checkQtyWarnings($productsIds);

        return $data;
    }

    protected function getSku(Ess_M2ePro_Model_Listing_Product_Variation $variation)
    {
        /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Variation $ebayVariation */
        $ebayVariation = $variation->getChildObject();

        if ($ebayVariation->getOnlineSku()) {
            return $ebayVariation->getOnlineSku();
        }

        $sku = $ebayVariation->getSku();

        if (strlen($sku) >= Ess_M2ePro_Helper_Component_Ebay::VARIATION_SKU_MAX_LENGTH) {
            $sku = Mage::helper('M2ePro')->hashString($sku, 'sha1', 'RANDOM_');
        }

        return $sku;
    }

    /**
     * @return bool|array
     */
    protected function getSetsData()
    {
        $additionalData = $this->getListingProduct()->getAdditionalData();

        if (isset($additionalData['variations_sets'])) {
            return $additionalData['variations_sets'];
        }

        return false;
    }

    protected function getVariationsThatCanNotBeDeleted()
    {
        $canNotBeDeleted = $this->_variationsThatCanNotBeDeleted;
        $additionalData = $this->getListingProduct()->getAdditionalData();

        if (isset($additionalData['variations_that_can_not_be_deleted'])) {
            $canNotBeDeleted = array_merge(
                $canNotBeDeleted, $additionalData['variations_that_can_not_be_deleted']
            );
        }

        return $canNotBeDeleted;
    }

    //########################################

    protected function getVariationPriceData(Ess_M2ePro_Model_Listing_Product_Variation $variation)
    {
        $priceData = array();

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Variation $ebayVariation */
        $ebayVariation = $variation->getChildObject();

        if (isset($this->validatorsData['variation_fixed_price_'.$variation->getId()])) {
            $priceData['price'] = $this->_cachedData['variation_fixed_price_' . $variation->getId()];
        } else {
            $priceData['price'] = $ebayVariation->getPrice();
        }

        if ($this->getEbayListingProduct()->isPriceDiscountStp()) {
            $priceDiscountData = array(
                'original_retail_price' => $ebayVariation->getPriceDiscountStp()
            );

            if ($this->getEbayMarketplace()->isStpAdvancedEnabled()) {
                $priceDiscountData = array_merge(
                    $priceDiscountData,
                    $this->getEbayListingProduct()->getEbaySellingFormatTemplate()
                        ->getPriceDiscountStpAdditionalFlags()
                );
            }

            $priceData['price_discount_stp'] = $priceDiscountData;
        }

        if ($this->getEbayListingProduct()->isPriceDiscountMap()) {
            $priceDiscountMapData = array(
                'minimum_advertised_price' => $ebayVariation->getPriceDiscountMap(),
            );

            $exposure = $ebayVariation->getEbaySellingFormatTemplate()->getPriceDiscountMapExposureType();
            $priceDiscountMapData['minimum_advertised_price_exposure'] =
                Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_Price::
                getPriceDiscountMapExposureType($exposure);

            $priceData['price_discount_map'] = $priceDiscountMapData;
        }

        return $priceData;
    }

    protected function logLimitationsAndReasons()
    {
        if ($this->getMagentoProduct()->isProductWithoutVariations()) {
            return;
        }

        if (!$this->getEbayMarketplace()->isMultivariationEnabled()) {
            $this->addWarningMessage(
                Mage::helper('M2ePro')->__(
                    'The Product was Listed as a Simple Product as it has limitation for Multi-Variation Items. '.
                    'Reason: Marketplace allows to list only Simple Items.'
                )
            );
            return;
        }

        $isVariationEnabled = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')
                                    ->isVariationEnabled(
                                        (int)$this->getCategorySource()->getCategoryId(),
                                        $this->getMarketplace()->getId()
                                    );

        if ($isVariationEnabled !== null && !$isVariationEnabled) {
            $this->addWarningMessage(
                Mage::helper('M2ePro')->__(
                    'The Product was Listed as a Simple Product as it has limitation for Multi-Variation Items. '.
                    'Reason: eBay Primary Category allows to list only Simple Items.'
                )
            );
            return;
        }

        if ($this->getEbayListingProduct()->getEbaySellingFormatTemplate()->isIgnoreVariationsEnabled()) {
            $this->addWarningMessage(
                Mage::helper('M2ePro')->__(
                    'The Product was Listed as a Simple Product as it has limitation for Multi-Variation Items. '.
                    'Reason: ignore Variation Option is enabled in Selling Policy.'
                )
            );
            return;
        }

        if (!$this->getEbayListingProduct()->isListingTypeFixed()) {
            $this->addWarningMessage(
                Mage::helper('M2ePro')->__(
                    'The Product was Listed as a Simple Product as it has limitation for Multi-Variation Items. '.
                    'Reason: Listing type "Auction" does not support Multi-Variations.'
                )
            );
            return;
        }
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category_Source
     */
    protected function getCategorySource()
    {
        return $this->getEbayListingProduct()->getCategoryTemplateSource();
    }

    //########################################

    public function checkQtyWarnings($productsIds)
    {
        $qtyMode = $this->getEbayListingProduct()->getEbaySellingFormatTemplate()->getQtyMode();
        if ($qtyMode == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED ||
            $qtyMode == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT) {
            $productsIds = array_unique($productsIds);
            $qtyWarnings = array();

            $listingProductId = $this->getListingProduct()->getId();
            $storeId = $this->getListing()->getStoreId();

            foreach ($productsIds as $productId) {
                if (!empty(
                    Ess_M2ePro_Model_Magento_Product::$statistics
                    [$listingProductId][$productId][$storeId]['qty']
                )) {
                    $qtys = Ess_M2ePro_Model_Magento_Product::$statistics
                        [$listingProductId][$productId][$storeId]['qty'];
                    $qtyWarnings = array_unique(array_merge($qtyWarnings, array_keys($qtys)));
                }

                if (count($qtyWarnings) === 2) {
                    break;
                }
            }

            foreach ($qtyWarnings as $qtyWarningType) {
                $this->addQtyWarnings($qtyWarningType);
            }
        }
    }

    /**
     * @param int $type
     */
    public function addQtyWarnings($type)
    {
        if ($type === Ess_M2ePro_Model_Magento_Product::FORCING_QTY_TYPE_MANAGE_STOCK_NO) {
            $this->addWarningMessage(
                'During the Quantity Calculation the Settings in the "Manage Stock No" '.
                'field were taken into consideration.'
            );
        }

        if ($type === Ess_M2ePro_Model_Magento_Product::FORCING_QTY_TYPE_BACKORDERS) {
            $this->addWarningMessage(
                'During the Quantity Calculation the Settings in the "Backorders" '.
                'field were taken into consideration.'
            );
        }
    }

    //########################################

    protected function getVariationDetails(Ess_M2ePro_Model_Listing_Product_Variation $variation)
    {
        $data = array();

        /** @var Ess_M2ePro_Model_Ebay_Template_Description $ebayDescriptionTemplate */
        $ebayDescriptionTemplate = $this->getEbayListingProduct()->getEbayDescriptionTemplate();

        $options = null;
        $additionalData = $variation->getAdditionalData();

        foreach (array('isbn','upc','ean','mpn','epid') as $tempType) {
            if ($tempType == 'mpn' && !empty($additionalData['online_product_details']['mpn'])) {
                $data['mpn'] = $additionalData['online_product_details']['mpn'];

                $isMpnCanBeChanged = Mage::helper('M2ePro/Component_Ebay_Configuration')
                    ->getVariationMpnCanBeChanged();

                if (!$isMpnCanBeChanged) {
                    continue;
                }

                $data['mpn_previous'] = $additionalData['online_product_details']['mpn'];
            }

            if (isset($additionalData['product_details'][$tempType])) {
                $data[$tempType] = $additionalData['product_details'][$tempType];
                continue;
            }

            if ($tempType == 'mpn') {
                if ($ebayDescriptionTemplate->isProductDetailsModeNone('brand')) {
                    continue;
                }

                if ($ebayDescriptionTemplate->isProductDetailsModeDoesNotApply('brand')) {
                    $data[$tempType] = Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_General::
                                                                            PRODUCT_DETAILS_DOES_NOT_APPLY;
                    continue;
                }
            }

            if ($ebayDescriptionTemplate->isProductDetailsModeNone($tempType)) {
                continue;
            }

            if ($ebayDescriptionTemplate->isProductDetailsModeDoesNotApply($tempType)) {
                $data[$tempType] = Ess_M2ePro_Model_Ebay_Listing_Product_Action_DataBuilder_General::
                                                                            PRODUCT_DETAILS_DOES_NOT_APPLY;
                continue;
            }

            if (!$this->getMagentoProduct()->isConfigurableType() &&
                !$this->getMagentoProduct()->isGroupedType()) {
                continue;
            }

            $attribute = $ebayDescriptionTemplate->getProductDetailAttribute($tempType);

            if (!$attribute) {
                continue;
            }

            if ($options === null) {
                $options = $variation->getOptions(true);
            }

            /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
            $option = reset($options);

            $this->searchNotFoundAttributes();
            $tempValue = $option->getMagentoProduct()->getAttributeValue($attribute);

            if (!$this->processNotFoundAttributes(strtoupper($tempType)) || !$tempValue) {
                continue;
            }

            $data[$tempType] = $tempValue;
        }

        return $this->deleteNotAllowedIdentifier($data);
    }

    protected function deleteNotAllowedIdentifier(array $data)
    {
        if (empty($data)) {
            return $data;
        }

        $categoryId = $this->getCategorySource()->getCategoryId();
        $marketplaceId = $this->getMarketplace()->getId();
        $categoryFeatures = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')
                                  ->getFeatures($categoryId, $marketplaceId);

        if (empty($categoryFeatures)) {
            return $data;
        }

        $statusDisabled = Ess_M2ePro_Helper_Component_Ebay_Category_Ebay::PRODUCT_IDENTIFIER_STATUS_DISABLED;

        foreach (array('ean','upc','isbn','epid') as $identifier) {
            $key = $identifier.'_enabled';
            if (!isset($categoryFeatures[$key]) || $categoryFeatures[$key] != $statusDisabled) {
                continue;
            }

            if (isset($data[$identifier])) {
                unset($data[$identifier]);

                $this->addWarningMessage(
                    Mage::helper('M2ePro')->__(
                        'The value of %type% was not sent because it is not allowed in this Category',
                        Mage::helper('M2ePro')->__(strtoupper($identifier))
                    )
                );
            }
        }

        return $data;
    }

    //########################################
}
