<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Listing_Product_Variation getParentObject()
 */
class Ess_M2ePro_Model_Ebay_Listing_Product_Variation extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Listing_Product_Variation');
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

    public function deleteInstance()
    {
        if ($this->getId() === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Method require loaded instance first');
        }

        if ($this->isLocked()) {
            return false;
        }

        $this->delete();
        return true;
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
     * @return Ess_M2ePro_Model_Ebay_Listing
     */
    public function getEbayListing()
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
     * @return Ess_M2ePro_Model_Ebay_Listing_Product
     */
    public function getEbayListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        return $this->getParentObject()->getAccount();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Account
     */
    public function getEbayAccount()
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
     * @return Ess_M2ePro_Model_Ebay_Marketplace
     */
    public function getEbayMarketplace()
    {
        return $this->getMarketplace()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        return $this->getEbayListingProduct()->getSellingFormatTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_SellingFormat
     */
    public function getEbaySellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        return $this->getEbayListingProduct()->getSynchronizationTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Synchronization
     */
    public function getEbaySynchronizationTemplate()
    {
        return $this->getSynchronizationTemplate()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Description
     */
    public function getDescriptionTemplate()
    {
        return $this->getEbayListingProduct()->getDescriptionTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Description
     */
    public function getEbayDescriptionTemplate()
    {
        return $this->getDescriptionTemplate()->getChildObject();
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Payment
     */
    public function getPaymentTemplate()
    {
        return $this->getEbayListingProduct()->getPaymentTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_ReturnPolicy
     */
    public function getReturnTemplate()
    {
        return $this->getEbayListingProduct()->getReturnTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Shipping
     */
    public function getShippingTemplate()
    {
        return $this->getEbayListingProduct()->getShippingTemplate();
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

    public function getOnlineSku()
    {
        return $this->getData('online_sku');
    }

    /**
     * @return float
     */
    public function getOnlinePrice()
    {
        return (float)$this->getData('online_price');
    }

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
    public function getOnlineQtySold()
    {
        return (int)$this->getData('online_qty_sold');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isAdd()
    {
        return (bool)$this->getData('add');
    }

    /**
     * @return bool
     */
    public function isDelete()
    {
        return (bool)$this->getData('delete');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getStatus()
    {
        return (int)$this->getData('status');
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        switch($status) {
            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                $status = $this->calculateStatusByQty();
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE:
            case Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN:
                $status = $this->calculateStatusByQty();
                if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                    $status = Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE;
                }
                break;

        }

        $this->getParentObject()->setData('status', $status)->save();
    }

    // ---------------------------------------

    /**
     * @return int
     */
    protected function calculateStatusByQty()
    {
        if ($this->getData('online_qty') === null) {
            return Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;
        }

        if ($this->getOnlineQty() == 0) {
            return Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN;
        } else if ($this->getOnlineQty() <= $this->getOnlineQtySold()) {
            return Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE;
        }

        return Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
    }

    //########################################

    /**
     * @return bool
     */
    public function isNotListed()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;
    }

    /**
     * @return bool
     */
    public function isBlocked()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isListed()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN;
    }

    /**
     * @return bool
     */
    public function isInactive()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_INACTIVE;
    }

    //########################################

    /**
     * @return string
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getSku()
    {
        if ($this->isDelete()) {
            return '';
        }

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

    /**
     * @param false $magentoMode
     * @return int
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getQty($magentoMode = false)
    {
        /** @var $calculator Ess_M2ePro_Model_Ebay_Listing_Product_QtyCalculator */
        $calculator = Mage::getModel('M2ePro/Ebay_Listing_Product_QtyCalculator');
        $calculator->setProduct($this->getListingProduct());
        $calculator->setIsMagentoMode($magentoMode);

        return $calculator->getVariationValue($this->getParentObject());
    }

    //########################################

    /**
     * @return float|int
     */
    public function getPrice()
    {
        $src = $this->getEbaySellingFormatTemplate()->getFixedPriceSource();

        $vatPercent = null;
        if ($this->getEbaySellingFormatTemplate()->isVatModeOnTopOfPrice()) {
            $vatPercent = $this->getEbaySellingFormatTemplate()->getVatPercent();
        }

        return $this->getCalculatedPrice(
            $src, $vatPercent, $this->getEbaySellingFormatTemplate()->getFixedPriceCoefficient()
        );
    }

    // ---------------------------------------

    /**
     * @return float|int
     */
    public function getPriceDiscountStp()
    {
        $src = $this->getEbaySellingFormatTemplate()->getPriceDiscountStpSource();

        $vatPercent = null;
        if ($this->getEbaySellingFormatTemplate()->isVatModeOnTopOfPrice()) {
            $vatPercent = $this->getEbaySellingFormatTemplate()->getVatPercent();
        }

        return $this->getCalculatedPrice($src, $vatPercent);
    }

    /**
     * @return float|int
     */
    public function getPriceDiscountMap()
    {
        $src = $this->getEbaySellingFormatTemplate()->getPriceDiscountMapSource();

        $vatPercent = null;
        if ($this->getEbaySellingFormatTemplate()->isVatModeOnTopOfPrice()) {
            $vatPercent = $this->getEbaySellingFormatTemplate()->getVatPercent();
        }

        return $this->getCalculatedPrice($src, $vatPercent);
    }

    // ---------------------------------------

    protected function getCalculatedPrice($src, $vatPercent = null, $coefficient = null)
    {
        /** @var $calculator Ess_M2ePro_Model_Ebay_Listing_Product_PriceCalculator */
        $calculator = Mage::getModel('M2ePro/Ebay_Listing_Product_PriceCalculator');
        $calculator->setSource($src)->setProduct($this->getListingProduct());
        $calculator->setVatPercent($vatPercent);
        $calculator->setCoefficient($coefficient);
        $calculator->setPriceVariationMode($this->getEbaySellingFormatTemplate()->getPriceVariationMode());

        return $calculator->getVariationValue($this->getParentObject());
    }

    //########################################

    /**
     * @return bool
     */
    public function hasSales()
    {
        $currentSpecifics = array();

        $options = $this->getOptions(true);
        foreach ($options as $option) {
            /** @var $option Ess_M2ePro_Model_Listing_Product_Variation_Option */
            $currentSpecifics[$option->getAttribute()] = $option->getOption();
        }

        ksort($currentSpecifics);
        $variationKeys = array_map('trim', array_keys($currentSpecifics));
        $variationValues = array_map('trim', array_values($currentSpecifics));

        $realEbayItemId = $this->getEbayListingProduct()->getEbayItem()->getItemId();

        $tempOrdersItemsCollection = Mage::getModel('M2ePro/Ebay_Order_Item')->getCollection();
        $tempOrdersItemsCollection->addFieldToFilter('item_id', $realEbayItemId);

        /** @var Ess_M2ePro_Model_Ebay_Order_Item[] $ordersItems */
        $ordersItems = $tempOrdersItemsCollection->getItems();

        $findOrderItem = false;

        foreach ($ordersItems as $orderItem) {
            $orderItemVariationOptions = $orderItem->getVariationProductOptions();

            if (empty($orderItemVariationOptions)) {
                continue;
            }

            ksort($orderItemVariationOptions);
            $orderItemVariationKeys = array_map('trim', array_keys($orderItemVariationOptions));
            $orderItemVariationValues = array_map('trim', array_values($orderItemVariationOptions));

            $diffKeys = array_diff($variationKeys, $orderItemVariationKeys);
            $diffValues = array_diff($variationValues, $orderItemVariationValues);

            if (count($currentSpecifics) == count($orderItemVariationOptions) &&
                empty($diffKeys) && empty($diffValues)
            ) {
                $findOrderItem = true;
                break;
            }
        }

        return $findOrderItem;
    }

    /*** @return int|null */
    public function getVariationProductId()
    {
        foreach ($this->getOptions(true) as $option) {
            if (!$option->getProductId()) {
                continue;
            }
            return $option->getProductId();
        }

        return null;
    }
}
