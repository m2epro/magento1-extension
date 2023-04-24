<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Listing_Product_PriceCalculator as PriceCalculator;
use Ess_M2ePro_Model_Walmart_Template_SellingFormat_Promotion as Promotion;
use Ess_M2ePro_Model_Resource_Walmart_Listing_Product as Resource;

/**
 * @method Ess_M2ePro_Model_Listing_Product getParentObject()
 */
class Ess_M2ePro_Model_Walmart_Listing_Product extends Ess_M2ePro_Model_Component_Child_Walmart_Abstract
{
    const INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED = 'channel_status_changed';
    const INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED    = 'channel_qty_changed';
    const INSTRUCTION_TYPE_CHANNEL_PRICE_CHANGED  = 'channel_price_changed';

    const PROMOTIONS_MAX_ALLOWED_COUNT = 10;

    /**
     * @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager
     */
    protected $_variationManager = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Walmart_Listing_Product');
    }

    //########################################

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        if ($this->getVariationManager()->isRelationParentType()) {
            foreach ($this->getVariationManager()->getTypeModel()->getChildListingsProducts() as $child) {
                /** @var $child Ess_M2ePro_Model_Listing_Product */
                if ($child->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        if ($this->getVariationManager()->isRelationParentType()) {
            foreach ($this->getVariationManager()->getTypeModel()->getChildListingsProducts() as $child) {
                /** @var $child Ess_M2ePro_Model_Listing_Product */
                $child->deleteInstance();
            }
        }

        $this->_variationManager = null;

        $this->delete();
        return true;
    }

    //########################################

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isVariationMode()
    {
        if ($this->hasData(__METHOD__)) {
            return $this->getData(__METHOD__);
        }

        $result = $this->getMagentoProduct()->isProductWithVariations();

        if ($this->getParentObject()->isGroupedProductModeSet()) {
            $result = false;
        }

        $this->setData(__METHOD__, $result);

        return $result;
    }

    /**
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function afterSaveNewEntity()
    {
        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager $variationManager */
        $variationManager = $this->getVariationManager();
        if ($variationManager->isVariationProduct() || !$this->isVariationMode()) {
            return null;
        }

        // m1 need to be added to parent
        $this->getParentObject()->setData('is_variation_product', 1);

        $variationManager->setRelationParentType();
        $variationManager->getTypeModel()->resetProductAttributes(false);
        $variationManager->getTypeModel()->getProcessor()->process();
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
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->getWalmartListing()->getSellingFormatTemplate();
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
        return $this->getWalmartListing()->getSynchronizationTemplate();
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
        return $this->getWalmartListing()->getDescriptionTemplate();
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
     * @return Ess_M2ePro_Model_Walmart_Template_SellingFormat_Source
     */
    public function getSellingFormatTemplateSource()
    {
        return $this->getWalmartSellingFormatTemplate()->getSource($this->getActualMagentoProduct());
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Template_Description_Source
     */
    public function getDescriptionTemplateSource()
    {
        return $this->getWalmartDescriptionTemplate()->getSource($this->getActualMagentoProduct());
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    public function getMagentoProduct()
    {
        return $this->getParentObject()->getMagentoProduct();
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    public function getActualMagentoProduct()
    {
        if (!$this->getVariationManager()->isPhysicalUnit() ||
            !$this->getVariationManager()->getTypeModel()->isVariationProductMatched()
        ) {
            return $this->getMagentoProduct();
        }

        if ($this->getMagentoProduct()->isConfigurableType() ||
            $this->getMagentoProduct()->isGroupedType() &&
            !$this->getParentObject()->isGroupedProductModeSet()) {
            $variations = $this->getVariations(true);
            if (empty($variations)) {
                throw new Ess_M2ePro_Model_Exception_Logic(
                    'There are no variations for a variation product.',
                    array(
                                                         'listing_product_id' => $this->getId()
                    )
                );
            }

            $variation  = reset($variations);
            $options    = $variation->getOptions(true);
            $option     = reset($options);

            return $option->getMagentoProduct();
        }

        return $this->getMagentoProduct();
    }

    /**
     * @param Ess_M2ePro_Model_Magento_Product_Cache $instance
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     * @throws Ess_M2ePro_Model_Exception
     */
    public function prepareMagentoProduct(Ess_M2ePro_Model_Magento_Product_Cache $instance)
    {
        if (!$this->getVariationManager()->isRelationMode()) {
            return $instance;
        }

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent $parentTypeModel */

        if ($this->getVariationManager()->isRelationParentType()) {
            $parentTypeModel = $this->getVariationManager()->getTypeModel();
        } else {
            $parentWalmartListingProduct = $this->getVariationManager()->getTypeModel()
                ->getWalmartParentListingProduct();
            $parentTypeModel = $parentWalmartListingProduct->getVariationManager()->getTypeModel();
        }

        $instance->setVariationVirtualAttributes($parentTypeModel->getVirtualProductAttributes());
        $instance->setVariationFilterAttributes($parentTypeModel->getVirtualChannelAttributes());

        return $instance;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Walmart_Item
    */
    public function getWalmartItem()
    {
        return Mage::getModel('M2ePro/Walmart_Item')->getCollection()
                        ->addFieldToFilter('account_id', $this->getListing()->getAccountId())
                        ->addFieldToFilter('marketplace_id', $this->getListing()->getMarketplaceId())
                        ->addFieldToFilter('sku', $this->getSku())
                        ->setOrder('create_date', Varien_Data_Collection::SORT_ORDER_DESC)
                        ->getFirstItem();
    }

    public function getVariationManager()
    {
        if ($this->_variationManager === null) {
            $this->_variationManager = Mage::getModel('M2ePro/Walmart_Listing_Product_Variation_Manager');
            $this->_variationManager->setListingProduct($this->getParentObject());
        }

        return $this->_variationManager;
    }

    /**
     * @param bool $asObjects
     * @param array $filters
     * @return Ess_M2ePro_Model_Listing_Product_Variation[]|array
     */
    public function getVariations($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getVariations($asObjects, $filters);
    }

    //########################################

    /**
     * @return int
     */
    public function getTemplateCategoryId()
    {
        return (int)($this->getData('template_category_id'));
    }

    /**
     * @return bool
     */
    public function isExistCategoryTemplate()
    {
        return $this->getTemplateCategoryId() > 0;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Template_Category | null
     */
    public function getCategoryTemplate()
    {
        if (!$this->isExistCategoryTemplate()) {
            return null;
        }

        return Mage::helper('M2ePro')->getCachedObject(
            'Walmart_Template_Category', $this->getTemplateCategoryId(), null, array('template')
        );
    }

    // ---------------------------------------

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->getData('sku');
    }

    /**
     * @return string
     */
    public function getGtin()
    {
        return $this->getData('gtin');
    }

    /**
     * @return string
     */
    public function getUpc()
    {
        return $this->getData('upc');
    }

    /**
     * @return string
     */
    public function getEan()
    {
        return $this->getData('ean');
    }

    /**
     * @return string
     */
    public function getIsbn()
    {
        return $this->getData('isbn');
    }

    /**
     * @return string
     */
    public function getWpid()
    {
        return $this->getData('wpid');
    }

    /**
     * @return string
     */
    public function getItemId()
    {
        return $this->getData('item_id');
    }

    // ---------------------------------------

    /**
     * @return string
     */
    public function getPublishStatus()
    {
        return $this->getData('publish_status');
    }

    /**
     * @return string
     */
    public function getLifecycleStatus()
    {
        return $this->getData('lifecycle_status');
    }

    /**
     * @return array
     */
    public function getStatusChangeReasons()
    {
        return $this->getSettings('status_change_reasons');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isStoppedManually()
    {
        return (bool)$this->getData(Resource::IS_STOPPED_MANUALLY_FIELD);
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isOnlinePriceInvalid()
    {
        return (bool)$this->getData('is_online_price_invalid');
    }

    // ---------------------------------------

    /**
     * @return float|null
     */
    public function getOnlinePrice()
    {
        return $this->getData('online_price');
    }

    /**
     * @return array
     */
    public function getOnlinePromotions()
    {
        return $this->getData('online_promotions');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getOnlineQty()
    {
        return (int)$this->getData('online_qty');
    }

    /**
     * @return int
     */
    public function getOnlineLagTime()
    {
        return (int)$this->getData('online_lag_time');
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getOnlineDetailsData()
    {
        return $this->getData('online_details_data');
    }

    // ---------------------------------------

    /**
     * @return string
     */
    public function getOnlineStartDate()
    {
        return $this->getData('online_start_date');
    }

    /**
     * @return string
     */
    public function getOnlineEndDate()
    {
        return $this->getData('online_end_date');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isMissedOnChannel()
    {
        return (bool)$this->getData('is_missed_on_channel');
    }

    // ---------------------------------------

    /**
     * @return string
     */
    public function getListDate()
    {
        return $this->getData('list_date');
    }

    //########################################

    /**
     * @param bool $magentoMode
     * @return int
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getQty($magentoMode = false)
    {
        if ($this->getVariationManager()->isPhysicalUnit() &&
            $this->getVariationManager()->getTypeModel()->isVariationProductMatched()) {
            $variations = $this->getVariations(true);
            if (empty($variations)) {
                throw new Ess_M2ePro_Model_Exception_Logic(
                    'There are no variations for a variation product.',
                    array(
                        'listing_product_id' => $this->getId()
                    )
                );
            }

            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation = reset($variations);

            return $variation->getChildObject()->getQty($magentoMode);
        }

        /** @var $calculator Ess_M2ePro_Model_Walmart_Listing_Product_QtyCalculator */
        $calculator = Mage::getModel('M2ePro/Walmart_Listing_Product_QtyCalculator');
        $calculator->setProduct($this->getParentObject());
        $calculator->setIsMagentoMode($magentoMode);

        return $calculator->getProductValue();
    }

    //########################################

    /**
     * @return float|int
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getPrice()
    {
        if ($this->getVariationManager()->isPhysicalUnit() &&
            $this->getVariationManager()->getTypeModel()->isVariationProductMatched()) {
            $variations = $this->getVariations(true);
            if (empty($variations)) {
                throw new Ess_M2ePro_Model_Exception_Logic(
                    'There are no variations for a variation product.',
                    array(
                        'listing_product_id' => $this->getId()
                    )
                );
            }

            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation = reset($variations);

            return $variation->getChildObject()->getPrice();
        }

        $src = $this->getWalmartSellingFormatTemplate()->getPriceSource();

        /** @var $calculator Ess_M2ePro_Model_Walmart_Listing_Product_PriceCalculator */
        $calculator = Mage::getModel('M2ePro/Walmart_Listing_Product_PriceCalculator');
        $calculator->setSource($src)->setProduct($this->getParentObject());
        $calculator->setCoefficient($this->getWalmartSellingFormatTemplate()->getPriceCoefficient());
        $calculator->setVatPercent($this->getWalmartSellingFormatTemplate()->getPriceVatPercent());

        return $calculator->getProductValue();
    }

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

        if ($this->getVariationManager()->isPhysicalUnit() &&
            $this->getVariationManager()->getTypeModel()->isVariationProductMatched()) {
            $variations = $this->getVariations(true);
            if (empty($variations)) {
                throw new Ess_M2ePro_Model_Exception_Logic(
                    'There are no variations for a variation product.',
                    array(
                        'listing_product_id' => $this->getId()
                    )
                );
            }

            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation = reset($variations);

            return $variation->getChildObject()->getPromotions();
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
            $priceCalculator->setSource($promotion->getPriceSource())->setProduct($this->getParentObject());
            $priceCalculator->setSourceModeMapping(
                array(
                PriceCalculator::MODE_PRODUCT   => Promotion::PRICE_MODE_PRODUCT,
                PriceCalculator::MODE_SPECIAL   => Promotion::PRICE_MODE_SPECIAL,
                PriceCalculator::MODE_ATTRIBUTE => Promotion::PRICE_MODE_ATTRIBUTE,
                )
            );
            $priceCalculator->setCoefficient($promotion->getPriceCoefficient());
            $priceCalculator->setVatPercent($this->getWalmartSellingFormatTemplate()->getPriceVatPercent());

            /** @var $comparisonPriceCalculator Ess_M2ePro_Model_Walmart_Listing_Product_PriceCalculator */
            $comparisonPriceCalculator = Mage::getModel('M2ePro/Walmart_Listing_Product_PriceCalculator');
            $comparisonPriceCalculator->setSource(
                $promotion->getComparisonPriceSource()
            )->setProduct(
                $this->getParentObject()
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

            $promotionSource = $promotion->getSource($this->getMagentoProduct());

            $resultPromotions[] = array(
                'start_date'       => $promotionSource->getStartDate(),
                'end_date'         => $promotionSource->getEndDate(),
                'price'            => $priceCalculator->getProductValue(),
                'comparison_price' => $comparisonPriceCalculator->getProductValue(),
                'type'             => strtoupper($promotion->getType())
            );

            if (count($resultPromotions) >= self::PROMOTIONS_MAX_ALLOWED_COUNT) {
                break;
            }
        }

        return $resultPromotions;
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getValidPromotions()
    {
        $promotionsData = $this->getValidPromotionsData();

        return $promotionsData['promotions'];
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getPromotionsErrorMessages()
    {
        $promotionsData = $this->getValidPromotionsData();

        return $promotionsData['messages'];
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    private function getValidPromotionsData()
    {
        $translationHelper = Mage::helper('M2ePro');
        $requiredAttributesMap = array(
            'start_date'       => $translationHelper->__('Start Date'),
            'end_date'         => $translationHelper->__('End Date'),
            'price'            => $translationHelper->__('Promotion Price'),
            'comparison_price' => $translationHelper->__('Comparison Price'),
        );

        $messages = array();
        $promotions = $this->getPromotions();

        foreach ($promotions as $promotionIndex => $promotionRow) {
            $isValidPromotion = true;

            foreach ($requiredAttributesMap as $requiredAttributeKey => $requiredAttributeTitle) {
                if (empty($promotionRow[$requiredAttributeKey])) {
                    $message = <<<HTML
Invalid Promotion #%s. The Promotion Price has no defined value.
 Please adjust Magento Attribute "%s" value set for the Promotion Price in your Selling Policy.
HTML;
                    $messages[] = sprintf($message, $promotionIndex + 1, $requiredAttributeTitle);
                    $isValidPromotion = false;
                }
            }

            if (!strtotime($promotionRow['start_date'])) {
                $message = <<<HTML
Invalid Promotion #%s. The Start Date has incorrect format.
 Please adjust Magento Attribute value set for the Promotion Start Date in your Selling Policy.
HTML;
                $messages[] = sprintf($message, $promotionIndex + 1);
                $isValidPromotion = false;
            }

            if (!strtotime($promotionRow['end_date'])) {
                $message = <<<HTML
Invalid Promotion #%s. The End Date has incorrect format.
 Please adjust Magento Attribute value set for the Promotion End Date in your Selling Policy.
HTML;
                $messages[] = sprintf($message, $promotionIndex + 1);
                $isValidPromotion = false;
            }

            /** @var Ess_M2ePro_Helper_Data $helper */
            $helper = Mage::helper('M2ePro');
            $endDateTimestamp = (int)$helper->createGmtDateTime($promotionRow['end_date'])->format('U');
            $startDateTimestamp = (int)$helper->createGmtDateTime($promotionRow['start_date'])->format('U');
            if ($endDateTimestamp < $startDateTimestamp) {
                $message = <<<HTML
Invalid Promotion #%s. The Start and End Date range is incorrect.
 Please adjust the Promotion Dates set in your Selling Policy.
HTML;
                $messages[] = sprintf($message, $promotionIndex + 1);
                $isValidPromotion = false;
            }

            if ($promotionRow['comparison_price'] <= $promotionRow['price']) {
                $message = <<<HTML
Invalid Promotion #%s. Comparison Price must be greater than Promotion Price.
 Please adjust the Price settings for the given Promotion in your Selling Policy.
HTML;
                $messages[] = sprintf($message, $promotionIndex + 1);
                $isValidPromotion = false;
            }

            if (!$isValidPromotion) {
                unset($promotions[$promotionIndex]);
            }
        }

        return array('messages' => $messages, 'promotions' => $promotions);
    }

    //########################################

    public function mapChannelItemProduct()
    {
        $this->getResource()->mapChannelItemProduct($this);
    }

    //########################################

    public function addVariationAttributes()
    {
        if (!$this->getVariationManager()->isRelationParentType()) {
            return;
        }

        $matchedAttributes = $this->findLocalMatchedAttributesByMagentoAttributes(
            $this->getVariationManager()->getTypeModel()->getProductAttributes()
        );

        if (empty($matchedAttributes)) {
            return;
        }

        $this->getVariationManager()->getTypeModel()->setMatchedAttributes($matchedAttributes);
        $this->getVariationManager()->getTypeModel()->setChannelAttributes(array_values($matchedAttributes));
        $this->getVariationManager()->getTypeModel()->getProcessor()->process();
    }

    private function findLocalMatchedAttributesByMagentoAttributes($magentoAttributes)
    {
        if (empty($magentoAttributes)) {
            return array();
        }

        $vocabularyHelper = Mage::helper('M2ePro/Component_Amazon_Vocabulary');
        $matchedAttributes = array();
        foreach ($magentoAttributes as $magentoAttr) {
            foreach ($vocabularyHelper->getLocalData() as $attribute => $attributeData) {
                if (in_array($magentoAttr, $attributeData['names'])) {
                    if (isset($matchedAttributes[$magentoAttr])) {
                        return array();
                    }
                    $matchedAttributes[$magentoAttr] = $attribute;
                }
            }
        }

        if (empty($matchedAttributes)) {
            return array();
        }

        if (count($magentoAttributes) != count($matchedAttributes)) {
            return array();
        }

        return $matchedAttributes;
    }

    //########################################
}
