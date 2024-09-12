<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Resource_Amazon_Listing_Product as Resource;

/**
 * @method Ess_M2ePro_Model_Listing_Product getParentObject()
 */
class Ess_M2ePro_Model_Amazon_Listing_Product extends Ess_M2ePro_Model_Component_Child_Amazon_Abstract
{
    const INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED        = 'channel_status_changed';
    const INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED           = 'channel_qty_changed';
    const INSTRUCTION_TYPE_CHANNEL_REGULAR_PRICE_CHANGED = 'channel_regular_price_changed';

    const IS_AFN_CHANNEL_NO  = 0;
    const IS_AFN_CHANNEL_YES = 1;

    const IS_REPRICING_NO  = 0;
    const IS_REPRICING_YES = 1;

    const VARIATION_PARENT_IS_AFN_STATE_ALL_NO  = 0;
    const VARIATION_PARENT_IS_AFN_STATE_PARTIAL = 1;
    const VARIATION_PARENT_IS_AFN_STATE_ALL_YES = 2;

    const VARIATION_PARENT_IS_REPRICING_STATE_ALL_NO  = 0;
    const VARIATION_PARENT_IS_REPRICING_STATE_PARTIAL = 1;
    const VARIATION_PARENT_IS_REPRICING_STATE_ALL_YES = 2;

    const IS_ISBN_GENERAL_ID_NO  = 0;
    const IS_ISBN_GENERAL_ID_YES = 1;

    const IS_GENERAL_ID_OWNER_NO  = 0;
    const IS_GENERAL_ID_OWNER_YES = 1;

    const SEARCH_SETTINGS_STATUS_IN_PROGRESS     = 1;
    const SEARCH_SETTINGS_STATUS_NOT_FOUND       = 2;
    const SEARCH_SETTINGS_STATUS_ACTION_REQUIRED = 3;
    const SEARCH_SETTINGS_IDENTIFIER_INVALID = 4;

    const GENERAL_ID_STATE_SET = 0;
    const GENERAL_ID_STATE_NOT_SET = 1;
    const GENERAL_ID_STATE_ACTION_REQUIRED = 2;
    const GENERAL_ID_STATE_READY_FOR_NEW_ASIN = 3;

    const BUSINESS_DISCOUNTS_MAX_RULES_COUNT_ALLOWED = 5;

    /**
     * @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager
     */
    protected $_variationManager = null;

    /**
     * @var Ess_M2ePro_Model_Amazon_Listing_Product_Repricing
     */
    protected $_repricingModel = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Amazon_Listing_Product');
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

        if ($this->isRepricingUsed()) {
            $this->getRepricing()->deleteInstance();
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
        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager $variationManager */
        $variationManager = $this->getVariationManager();
        if ($variationManager->isVariationProduct() || !$this->isVariationMode()) {
            return null;
        }

        // need to be added to parent
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
     * @return Ess_M2ePro_Model_Amazon_Listing_Source
     */
    public function getListingSource()
    {
        return $this->getAmazonListing()->getSource($this->getActualMagentoProduct());
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->getAmazonListing()->getSellingFormatTemplate();
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
        return $this->getAmazonListing()->getSynchronizationTemplate();
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
     * @return bool
     */
    public function isExistShippingTemplate()
    {
        return $this->getTemplateShippingId() > 0;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Shipping | null
     */
    public function getShippingTemplate()
    {
        if (!$this->isExistShippingTemplate()) {
            return null;
        }

        return Mage::helper('M2ePro')->getCachedObject(
            'Amazon_Template_Shipping', $this->getTemplateShippingId(), null, array('template')
        );
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Shipping_Source
     */
    public function getShippingTemplateSource()
    {
        if (!$this->isExistShippingTemplate()) {
            return null;
        }

        return $this->getShippingTemplate()->getSource($this->getActualMagentoProduct());
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isExistProductTaxCodeTemplate()
    {
        return $this->getTemplateProductTaxCodeId() > 0;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_ProductTaxCode | null
     */
    public function getProductTaxCodeTemplate()
    {
        if (!$this->isExistProductTaxCodeTemplate()) {
            return null;
        }

        return Mage::helper('M2ePro')->getCachedObject(
            'Amazon_Template_ProductTaxCode', $this->getTemplateProductTaxCodeId(), null, array('template')
        );
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_ProductTaxCode_Source
     */
    public function getProductTaxCodeTemplateSource()
    {
        if (!$this->isExistProductTaxCodeTemplate()) {
            return null;
        }

        return $this->getProductTaxCodeTemplate()->getSource($this->getActualMagentoProduct());
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isExistDescriptionTemplate()
    {
        return $this->getTemplateDescriptionId() > 0;
    }

    /**
     * @return Ess_M2ePro_Model_Template_Description | null
     */
    public function getDescriptionTemplate()
    {
        if (!$this->isExistDescriptionTemplate()) {
            return null;
        }

        return Mage::helper('M2ePro/Component_Amazon')->getCachedObject(
            'Template_Description', $this->getTemplateDescriptionId(), null, array('template')
        );
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Description | null
     */
    public function getAmazonDescriptionTemplate()
    {
        if (!$this->isExistDescriptionTemplate()) {
            return null;
        }

        return $this->getDescriptionTemplate()->getChildObject();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Description_Source
     */
    public function getDescriptionTemplateSource()
    {
        if (!$this->isExistDescriptionTemplate()) {
            return null;
        }

        return $this->getAmazonDescriptionTemplate()->getSource($this->getActualMagentoProduct());
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

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent $parentTypeModel */

        if ($this->getVariationManager()->isRelationParentType()) {
            $parentTypeModel = $this->getVariationManager()->getTypeModel();
        } else {
            $parentAmazonListingProduct = $this->getVariationManager()->getTypeModel()->getAmazonParentListingProduct();
            $parentTypeModel = $parentAmazonListingProduct->getVariationManager()->getTypeModel();
        }

        $instance->setVariationVirtualAttributes($parentTypeModel->getVirtualProductAttributes());
        $instance->setVariationFilterAttributes($parentTypeModel->getVirtualChannelAttributes());

        return $instance;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Item
    */
    public function getAmazonItem()
    {
        return Mage::getModel('M2ePro/Amazon_Item')->getCollection()
                        ->addFieldToFilter('account_id', $this->getListing()->getAccountId())
                        ->addFieldToFilter('marketplace_id', $this->getListing()->getMarketplaceId())
                        ->addFieldToFilter('sku', $this->getSku())
                        ->setOrder('create_date', Varien_Data_Collection::SORT_ORDER_DESC)
                        ->getFirstItem();
    }

    public function getVariationManager()
    {
        if ($this->_variationManager === null) {
            $this->_variationManager = Mage::getModel('M2ePro/Amazon_Listing_Product_Variation_Manager');
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
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Repricing
     */
    public function getRepricing()
    {
        if ($this->_repricingModel === null) {
            $this->_repricingModel = Mage::getModel('M2ePro/Amazon_Listing_Product_Repricing')->load($this->getId());
        }

        return $this->_repricingModel;
    }

    /**
     * @return bool
     */
    public function isRepricingUsed()
    {
        return $this->isRepricing() && $this->getRepricing()->getId();
    }

    /**
     * @return bool
     */
    public function isRepricingManaged()
    {
        return $this->isRepricingUsed() &&
            !$this->getRepricing()->isOnlineDisabled() && !$this->getRepricing()->isOnlineInactive();
    }

    /**
     * @return bool
     */
    public function isRepricingDisabled()
    {
        return $this->isRepricingUsed() && $this->getRepricing()->isOnlineDisabled();
    }

    /**
     * @return bool
     */
    public function isRepricingInactive()
    {
        return $this->isRepricingUsed() && $this->getRepricing()->isOnlineInactive();
    }

    //########################################

    /**
     * @return int
     */
    public function getTemplateDescriptionId()
    {
        return (int)($this->getData('template_description_id'));
    }

    /**
     * @return int
     */
    public function getTemplateShippingId()
    {
        return (int)($this->getData('template_shipping_id'));
    }

    /**
     * @return int
     */
    public function getTemplateProductTaxCodeId()
    {
        return (int)($this->getData('template_product_tax_code_id'));
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
    public function getGeneralId()
    {
        return $this->getData('general_id');
    }

    // ---------------------------------------

    /**
     * @return float|null
     */
    public function getOnlineRegularPrice()
    {
        return $this->getData('online_regular_price');
    }

    public function getOnlineRegularSalePrice()
    {
        return $this->getData('online_regular_sale_price');
    }

    public function getOnlineRegularSalePriceStartDate()
    {
        return $this->getData('online_regular_sale_price_start_date');
    }

    public function getOnlineRegularSalePriceEndDate()
    {
        return $this->getData('online_regular_sale_price_end_date');
    }

    // ---------------------------------------

    /**
     * @return float|null
     */
    public function getOnlineBusinessPrice()
    {
        return (float)$this->getData('online_business_price');
    }

    public function getOnlineBusinessDiscounts()
    {
        return $this->getSettings('online_business_discounts');
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
     * @return int|null
     */
    public function getOnlineHandlingTime()
    {
        $handlingTime = $this->getData('online_handling_time');
        if ($handlingTime === null) {
            return null;
        }

        return (int)$handlingTime;
    }

    public function getOnlineRestockDate()
    {
        return $this->getData('online_restock_date');
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getOnlineDetailsData()
    {
        return $this->getData('online_details_data');
    }

    /**
     * @return array
     */
    public function getOnlineImagesData()
    {
        return $this->getData('online_images_data');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isRepricing()
    {
        return (int)$this->getData('is_repricing') == self::IS_REPRICING_YES;
    }

    /**
     * @return bool
     */
    public function isAfnChannel()
    {
        return (int)$this->getData('is_afn_channel') == self::IS_AFN_CHANNEL_YES;
    }

    /**
     * @return bool
     */
    public function isIsbnGeneralId()
    {
        return (int)$this->getData('is_isbn_general_id') == self::IS_ISBN_GENERAL_ID_YES;
    }

    /**
     * @return bool
     */
    public function isGeneralIdOwner()
    {
        return (int)$this->getData('is_general_id_owner') == self::IS_GENERAL_ID_OWNER_YES;
    }

    // ---------------------------------------

    public function getVariationParentAfnState()
    {
        return $this->getData('variation_parent_afn_state');
    }

    public function isVariationParentAfnStateNo()
    {
        return (int)$this->getVariationParentAfnState() == self::VARIATION_PARENT_IS_AFN_STATE_ALL_NO;
    }

    public function isVariationParentAfnStatePartial()
    {
        return (int)$this->getVariationParentAfnState() == self::VARIATION_PARENT_IS_AFN_STATE_PARTIAL;
    }

    public function isVariationParentAfnStateYes()
    {
        return (int)$this->getVariationParentAfnState() == self::VARIATION_PARENT_IS_AFN_STATE_ALL_YES;
    }

    // ---------------------------------------

    public function getVariationParentRepricingState()
    {
        return $this->getData('variation_parent_repricing_state');
    }

    public function isVariationParentRepricingStateNo()
    {
        return (int)$this->getVariationParentRepricingState() == self::VARIATION_PARENT_IS_REPRICING_STATE_ALL_NO;
    }

    public function isVariationParentRepricingStatePartial()
    {
        return (int)$this->getVariationParentRepricingState() == self::VARIATION_PARENT_IS_REPRICING_STATE_PARTIAL;
    }

    public function isVariationParentRepricingStateYes()
    {
        return (int)$this->getVariationParentRepricingState() == self::VARIATION_PARENT_IS_REPRICING_STATE_ALL_YES;
    }

    // ---------------------------------------

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getDefectedMessages()
    {
        return $this->getSettings('defected_messages');
    }

    //########################################

    public function getSearchSettingsStatus()
    {
        return $this->getData('search_settings_status');
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getSearchSettingsData()
    {
        return $this->getSettings('search_settings_data');
    }

    // ---------------------------------------

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getGeneralIdSearchInfo()
    {
        return $this->getSettings('general_id_search_info');
    }

    //########################################

    public function isAllowedForRegularCustomers()
    {
        return $this->getAmazonSellingFormatTemplate()->isRegularCustomerAllowed();
    }

    public function isAllowedForBusinessCustomers()
    {
        if (!Mage::helper('M2ePro/Component_Amazon_Configuration')->isEnabledBusinessMode()) {
            return false;
        }

        if (!$this->getAmazonMarketplace()->isBusinessAvailable()) {
            return false;
        }

        if (!$this->getAmazonSellingFormatTemplate()->isBusinessCustomerAllowed()) {
            return false;
        }

        return true;
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

        /** @var $calculator Ess_M2ePro_Model_Amazon_Listing_Product_QtyCalculator */
        $calculator = Mage::getModel('M2ePro/Amazon_Listing_Product_QtyCalculator');
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
    public function getRegularPrice()
    {
        if (!$this->isAllowedForRegularCustomers()) {
            return null;
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

            return $variation->getChildObject()->getRegularPrice();
        }

        $src = $this->getAmazonSellingFormatTemplate()->getRegularPriceSource();

        /** @var $calculator Ess_M2ePro_Model_Amazon_Listing_Product_PriceCalculator */
        $calculator = Mage::getModel('M2ePro/Amazon_Listing_Product_PriceCalculator');
        $calculator->setSource($src)->setProduct($this->getParentObject());
        $calculator->setCoefficient($this->getAmazonSellingFormatTemplate()->getRegularPriceCoefficient());
        $calculator->setVatPercent($this->getAmazonSellingFormatTemplate()->getRegularPriceVatPercent());

        return $calculator->getProductValue();
    }

    /**
     * @return float|int
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getRegularMapPrice()
    {
        if (!$this->isAllowedForRegularCustomers()) {
            return null;
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

            return $variation->getChildObject()->getRegularMapPrice();
        }

        $src = $this->getAmazonSellingFormatTemplate()->getRegularMapPriceSource();

        /** @var $calculator Ess_M2ePro_Model_Amazon_Listing_Product_PriceCalculator */
        $calculator = Mage::getModel('M2ePro/Amazon_Listing_Product_PriceCalculator');
        $calculator->setSource($src)->setProduct($this->getParentObject());

        return $calculator->getProductValue();
    }

    // ---------------------------------------

    /**
     * @return float|int
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getRegularSalePrice()
    {
        if (!$this->isAllowedForRegularCustomers()) {
            return null;
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

            return $variation->getChildObject()->getRegularSalePrice();
        }

        $src = $this->getAmazonSellingFormatTemplate()->getRegularSalePriceSource();

        /** @var $calculator Ess_M2ePro_Model_Amazon_Listing_Product_PriceCalculator */
        $calculator = Mage::getModel('M2ePro/Amazon_Listing_Product_PriceCalculator');
        $calculator->setSource($src)->setProduct($this->getParentObject());
        $calculator->setIsSalePrice(true);
        $calculator->setCoefficient($this->getAmazonSellingFormatTemplate()->getRegularSalePriceCoefficient());
        $calculator->setVatPercent($this->getAmazonSellingFormatTemplate()->getRegularPriceVatPercent());

        return $calculator->getProductValue();
    }

    /**
     * @return array|bool
     */
    public function getRegularSalePriceInfo()
    {
        $price = $this->getRegularPrice();
        $salePrice = $this->getRegularSalePrice();

        if ($salePrice <= 0 || $salePrice >= $price) {
            return false;
        }

        $startDate = $this->getRegularSalePriceStartDate();
        $endDate = $this->getRegularSalePriceEndDate();

        if (!$startDate || !$endDate) {
            return false;
        }

        /** @var Ess_M2ePro_Helper_Data $helper */
        $helper = Mage::helper('M2ePro');

        $startDateTimestamp = (int)$helper->createGmtDateTime($startDate)
            ->format('U');
        $endDateTimestamp = (int)$helper->createGmtDateTime($endDate)
            ->format('U');

        $currentTimestamp = (int)$helper->createGmtDateTime(
            $helper->getCurrentGmtDate(false, 'Y-m-d 00:00:00')
        )->format('U');

        if ($currentTimestamp > $endDateTimestamp ||
            $startDateTimestamp >= $endDateTimestamp
        ) {
            return false;
        }

        return array(
            'price'      => $salePrice,
            'start_date' => $startDate,
            'end_date'   => $endDate
        );
    }

    // ---------------------------------------

    protected function getRegularSalePriceStartDate()
    {
        if ($this->getAmazonSellingFormatTemplate()->isRegularSalePriceModeSpecial() &&
            $this->getMagentoProduct()->isGroupedType()) {
            $magentoProduct = $this->getActualMagentoProduct();
        } else if ($this->getAmazonSellingFormatTemplate()->isRegularPriceVariationModeParent()) {
            $magentoProduct = $this->getMagentoProduct();
        } else {
            $magentoProduct = $this->getActualMagentoProduct();
        }

        $date = null;

        if ($this->getAmazonSellingFormatTemplate()->isRegularSalePriceModeSpecial()) {
            $date = $magentoProduct->getSpecialPriceFromDate();
        } else {
            $src = $this->getAmazonSellingFormatTemplate()->getRegularSalePriceStartDateSource();

            $date = $src['value'];

            if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_SellingFormat::DATE_ATTRIBUTE) {
                $date = $magentoProduct->getAttributeValue($src['attribute']);
            }
        }

        if (strtotime($date) === false) {
            return false;
        }

        return Mage::helper('M2ePro')->createGmtDateTime($date)->format('Y-m-d 00:00:00');
    }

    protected function getRegularSalePriceEndDate()
    {
        if ($this->getAmazonSellingFormatTemplate()->isRegularSalePriceModeSpecial() &&
            $this->getMagentoProduct()->isGroupedType()) {
            $magentoProduct = $this->getActualMagentoProduct();
        } else if ($this->getAmazonSellingFormatTemplate()->isRegularPriceVariationModeParent()) {
            $magentoProduct = $this->getMagentoProduct();
        } else {
            $magentoProduct = $this->getActualMagentoProduct();
        }

        $date = null;

        if ($this->getAmazonSellingFormatTemplate()->isRegularSalePriceModeSpecial()) {
            $date = $magentoProduct->getSpecialPriceToDate();

            $tempDate = new DateTime($date, new DateTimeZone('UTC'));
            $tempDate->modify('-1 day');
            $date = $tempDate->format('Y-m-d H:i:s');
        } else {
            $src = $this->getAmazonSellingFormatTemplate()->getRegularSalePriceEndDateSource();

            $date = $src['value'];

            if ($src['mode'] == Ess_M2ePro_Model_Amazon_Template_SellingFormat::DATE_ATTRIBUTE) {
                $date = $magentoProduct->getAttributeValue($src['attribute']);
            }
        }

        if (strtotime($date) === false) {
            return false;
        }

        return Mage::helper('M2ePro')->createGmtDateTime($date)->format('Y-m-d 00:00:00');
    }

    // ---------------------------------------

    /**
     * @return float|int
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getBusinessPrice()
    {
        if (!$this->isAllowedForBusinessCustomers()) {
            return null;
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

            return $variation->getChildObject()->getBusinessPrice();
        }

        $src = $this->getAmazonSellingFormatTemplate()->getBusinessPriceSource();

        /** @var $calculator Ess_M2ePro_Model_Amazon_Listing_Product_PriceCalculator */
        $calculator = Mage::getModel('M2ePro/Amazon_Listing_Product_PriceCalculator');
        $calculator->setSource($src)->setProduct($this->getParentObject());
        $calculator->setCoefficient($this->getAmazonSellingFormatTemplate()->getBusinessPriceCoefficient());
        $calculator->setVatPercent($this->getAmazonSellingFormatTemplate()->getBusinessPriceVatPercent());

        return $calculator->getProductValue();
    }

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getBusinessDiscounts()
    {
        if (!$this->isAllowedForBusinessCustomers()) {
            return null;
        }

        if ($this->getAmazonSellingFormatTemplate()->isBusinessDiscountsModeNone()) {
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

            return $variation->getChildObject()->getBusinessDiscounts();
        }

        if ($this->getAmazonSellingFormatTemplate()->isBusinessDiscountsModeTier()) {
            $src = $this->getAmazonSellingFormatTemplate()->getBusinessDiscountsSource();

            $storeId = $this->getListing()->getStoreId();
            $src['tier_website_id'] = Mage::helper('M2ePro/Magento_Store')->getWebsite($storeId)->getId();

            /** @var $calculator Ess_M2ePro_Model_Amazon_Listing_Product_PriceCalculator */
            $calculator = Mage::getModel('M2ePro/Amazon_Listing_Product_PriceCalculator');
            $calculator->setSource($src)->setProduct($this->getParentObject());
            $calculator->setSourceModeMapping(
                array(
                Ess_M2ePro_Model_Listing_Product_PriceCalculator::MODE_TIER
                    => Ess_M2ePro_Model_Amazon_Template_SellingFormat::BUSINESS_DISCOUNTS_MODE_TIER,
                )
            );
            $calculator->setCoefficient($this->getAmazonSellingFormatTemplate()->getBusinessDiscountsTierCoefficient());
            $calculator->setVatPercent($this->getAmazonSellingFormatTemplate()->getBusinessPriceVatPercent());

            return array_slice(
                $calculator->getProductValue(), 0, self::BUSINESS_DISCOUNTS_MAX_RULES_COUNT_ALLOWED, true
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
            $calculator->setSource($businessDiscount->getSource())->setProduct($this->getParentObject());
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

            $resultValue[$businessDiscount->getQty()] = $calculator->getProductValue();

            if (count($resultValue) >= self::BUSINESS_DISCOUNTS_MAX_RULES_COUNT_ALLOWED) {
                break;
            }
        }

        return $resultValue;
    }

    //########################################

    public function mapChannelItemProduct()
    {
        $this->getResource()->mapChannelItemProduct($this);
    }

    //########################################

    /**
     * @return bool
     */
    public function isStoppedManually()
    {
        return (bool)$this->getData(Resource::IS_STOPPED_MANUALLY_FIELD);
    }

    //########################################
}
