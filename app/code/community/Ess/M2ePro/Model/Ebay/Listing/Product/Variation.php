<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
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
     * @return Ess_M2ePro_Model_Ebay_Template_Return
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
     * @param array $filters
     * @return Ess_M2ePro_Model_Listing_Product_Variation_Option[]
     */
    public function getOptions($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getOptions($asObjects,$filters);
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

            case Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN:
                $status = $this->calculateStatusByQty();
                if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                    $status = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
                }
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_SOLD:
                $status = $this->calculateStatusByQty();
                if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                    $status = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
                }
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                $status = $this->calculateStatusByQty();
                if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                    $status = Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
                }
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED:
                $status = $this->calculateStatusByQty();
                if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                    $status = Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED;
                }
                break;
        }

        $this->getParentObject()->setData('status' , $status)->save();
    }

    // ---------------------------------------

    /**
     * @return int
     */
    private function calculateStatusByQty()
    {
        if (is_null($this->getData('online_qty'))) {
            return Ess_M2ePro_Model_Listing_Product::STATUS_NOT_LISTED;
        }

        if ($this->getOnlineQty() == 0) {
            return Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN;
        } else if ($this->getOnlineQty() <= $this->getOnlineQtySold()) {
            return Ess_M2ePro_Model_Listing_Product::STATUS_SOLD;
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
    public function isUnknown()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_UNKNOWN;
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
    public function isSold()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_SOLD;
    }

    /**
     * @return bool
     */
    public function isStopped()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED;
    }

    /**
     * @return bool
     */
    public function isFinished()
    {
        return $this->getStatus() == Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED;
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
        }

        if (strlen($sku) >= 80) {
            $sku = 'RANDOM_'.sha1($sku);
        }

        return $sku;
    }

    /**
     * @return int
     */
    public function getQty()
    {
        /** @var $calculator Ess_M2ePro_Model_Ebay_Listing_Product_QtyCalculator */
        $calculator = Mage::getModel('M2ePro/Ebay_Listing_Product_QtyCalculator');
        $calculator->setProduct($this->getListingProduct());
        return $calculator->getVariationValue($this->getParentObject());
    }

    //########################################

    /**
     * @return float|int
     */
    public function getPrice()
    {
        $src = $this->getEbaySellingFormatTemplate()->getFixedPriceSource();
        return $this->getCalculatedPrice($src, true, true);
    }

    // ---------------------------------------

    /**
     * @return float|int
     */
    public function getPriceDiscountStp()
    {
        $src = $this->getEbaySellingFormatTemplate()->getPriceDiscountStpSource();
        return $this->getCalculatedPrice($src, true, false);
    }

    /**
     * @return float|int
     */
    public function getPriceDiscountMap()
    {
        $src = $this->getEbaySellingFormatTemplate()->getPriceDiscountMapSource();
        return $this->getCalculatedPrice($src, true, false);
    }

    // ---------------------------------------

    private function getCalculatedPrice($src, $increaseByVatPercent = false, $modifyByCoefficient = false)
    {
        /** @var $calculator Ess_M2ePro_Model_Ebay_Listing_Product_PriceCalculator */
        $calculator = Mage::getModel('M2ePro/Ebay_Listing_Product_PriceCalculator');
        $calculator->setSource($src)->setProduct($this->getListingProduct());
        $calculator->setIsIncreaseByVatPercent($increaseByVatPercent);
        $calculator->setModifyByCoefficient($modifyByCoefficient);
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

            if (count($currentSpecifics) == count($orderItemVariationOptions) &&
                count(array_diff($variationKeys,$orderItemVariationKeys)) <= 0 &&
                count(array_diff($variationValues,$orderItemVariationValues)) <= 0) {
                $findOrderItem = true;
                break;
            }
        }

        return $findOrderItem;
    }

    //########################################
}