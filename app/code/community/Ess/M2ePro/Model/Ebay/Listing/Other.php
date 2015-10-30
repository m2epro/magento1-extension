<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Listing_Other getParentObject()
 */
class Ess_M2ePro_Model_Ebay_Listing_Other extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Listing_Other');
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
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        return $this->getParentObject()->getMarketplace();
    }

    /**
     * @return Ess_M2ePro_Model_Magento_Product_Cache
     */
    public function getMagentoProduct()
    {
        return $this->getParentObject()->getMagentoProduct();
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Other_Source
     */
    public function getSourceModel()
    {
        return Mage::getSingleton('M2ePro/Ebay_Listing_Other_Source');
    }

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Other_Synchronization
     */
    public function getSynchronizationModel()
    {
        return Mage::getSingleton('M2ePro/Ebay_Listing_Other_Synchronization');
    }

    //########################################

    public function getSku()
    {
        return $this->getData('sku');
    }

    /**
     * @return float
     */
    public function getItemId()
    {
        return (double)$this->getData('item_id');
    }

    // ---------------------------------------

    public function getTitle()
    {
        return $this->getData('title');
    }

    public function getCurrency()
    {
        return $this->getData('currency');
    }

    // ---------------------------------------

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

    /**
     * @return int
     */
    public function getOnlineBids()
    {
        return (int)$this->getData('online_bids');
    }

    // ---------------------------------------

    public function getStartDate()
    {
        return $this->getData('start_date');
    }

    public function getEndDate()
    {
        return $this->getData('end_date');
    }

    //########################################

    /**
     * @return float|int|null
     */
    public function getMappedPrice()
    {
        if (is_null($this->getParentObject()->getProductId()) ||
            $this->getMagentoProduct()->isProductWithVariations() ||
            $this->getSourceModel()->isPriceSourceNone()) {
            return NULL;
        }

        $price = 0;

        if ($this->getSourceModel()->isPriceSourceProduct()) {
            $price = $this->getMagentoProduct()->getPrice();
            $price = $this->convertPriceFromStoreToMarketplace($price);
        }

        if ($this->getSourceModel()->isPriceSourceSpecial()) {
            $price = (float)$this->getMagentoProduct()->getSpecialPrice();
            $price <= 0 && $price = $this->getMagentoProduct()->getPrice();
            $price = $this->convertPriceFromStoreToMarketplace($price);
        }

        if ($this->getSourceModel()->isPriceSourceAttribute()) {
            $attribute = $this->getSourceModel()->getPriceAttribute();
            $price = $this->getMagentoProduct()->getAttributeValue($attribute);
        }

        $price < 0 && $price = 0;

        return $price;
    }

    /**
     * @return int|null
     */
    public function getMappedQty()
    {
        if (is_null($this->getParentObject()->getProductId()) ||
            $this->getMagentoProduct()->isProductWithVariations() ||
            $this->getSourceModel()->isQtySourceNone()) {
            return NULL;
        }

        $qty = 0;

        if ($this->getSourceModel()->isQtySourceProduct()) {
            $qty = (int)$this->getMagentoProduct()->getQty(true);
        }

        if ($this->getSourceModel()->isQtySourceProductFixed()) {
            $qty = (int)$this->getMagentoProduct()->getQty(false);
        }

        if ($this->getSourceModel()->isQtySourceAttribute()) {
            $attribute = $this->getSourceModel()->getQtyAttribute();
            $qty = (int)$this->getMagentoProduct()->getAttributeValue($attribute);
        }

        $qty < 0 && $qty = 0;

        return (int)floor($qty);
    }

    // ---------------------------------------

    /**
     * @return null|string
     */
    public function getMappedTitle()
    {
        if (is_null($this->getParentObject()->getProductId()) ||
            $this->getSourceModel()->isTitleSourceNone()) {
            return NULL;
        }

        $title = '';

        if ($this->getSourceModel()->isTitleSourceProduct()) {
            $title = $this->getMagentoProduct()->getName();
        }

        if ($this->getSourceModel()->isTitleSourceAttribute()) {
            $attribute = $this->getSourceModel()->getTitleAttribute();
            $title = $this->getMagentoProduct()->getAttributeValue($attribute);
        }

        return $title;
    }

    /**
     * @return null|string
     */
    public function getMappedSubTitle()
    {
        if (is_null($this->getParentObject()->getProductId()) ||
            $this->getSourceModel()->isSubTitleSourceNone()) {
            return NULL;
        }

        $subTitle = '';

        if ($this->getSourceModel()->isSubTitleSourceAttribute()) {
            $attribute = $this->getSourceModel()->getSubTitleAttribute();
            $subTitle = $this->getMagentoProduct()->getAttributeValue($attribute);
        }

        return $subTitle;
    }

    /**
     * @return string|null
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getMappedDescription()
    {
        if (is_null($this->getParentObject()->getProductId()) ||
            $this->getSourceModel()->isDescriptionSourceNone()) {
            return NULL;
        }

        $description = '';
        $templateProcessor = Mage::getModel('Core/Email_Template_Filter');

        if ($this->getSourceModel()->isDescriptionSourceProductMain()) {
            $description = $this->getMagentoProduct()->getProduct()->getDescription();
            $description = $templateProcessor->filter($description);
        }

        if ($this->getSourceModel()->isDescriptionSourceProductShort()) {
            $description = $this->getMagentoProduct()->getProduct()->getShortDescription();
            $description = $templateProcessor->filter($description);
        }

        if ($this->getSourceModel()->isDescriptionSourceAttribute()) {
            $attribute = $this->getSourceModel()->getDescriptionAttribute();
            $description = $this->getMagentoProduct()->getAttributeValue($attribute);
        }

        return str_replace(array('<![CDATA[', ']]>'), '', $description);
    }

    //########################################

    public function getRelatedStoreId()
    {
        return $this->getAccount()->getChildObject()->getRelatedStoreId($this->getParentObject()->getMarketplaceId());
    }

    public function convertPriceFromStoreToMarketplace($price)
    {
        return Mage::getSingleton('M2ePro/Currency')->convertPrice(
            $price,
            $this->getMarketplace()->getChildObject()->getCurrency(),
            $this->getRelatedStoreId()
        );
    }

    //########################################

    public function reviseAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,$params);
    }

    public function relistAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Listing_Product::ACTION_RELIST,$params);
    }

    public function stopAction(array $params = array())
    {
        return $this->processDispatcher(Ess_M2ePro_Model_Listing_Product::ACTION_STOP,$params);
    }

    // ---------------------------------------

    protected function processDispatcher($action, array $params = array())
    {
        if (is_null($this->getId())) {
             throw new Ess_M2ePro_Model_Exception('Method require loaded instance first');
        }

        $dispatcher = Mage::getModel('M2ePro/Connector_Ebay_OtherItem_Dispatcher');

        return $dispatcher->process($action, $this->getId(), $params);
    }

    //########################################

    public function afterMapProduct()
    {
        $dataForAdd = array(
            'account_id' => $this->getAccount()->getId(),
            'marketplace_id' => $this->getMarketplace()->getId(),
            'item_id' => $this->getItemId(),
            'product_id' => $this->getParentObject()->getProductId(),
            'store_id' => $this->getRelatedStoreId()
        );

        Mage::getModel('M2ePro/Ebay_Item')->setData($dataForAdd)->save();
    }

    public function beforeUnmapProduct()
    {
        Mage::getSingleton('core/resource')->getConnection('core_write')
            ->delete(Mage::getResourceModel('M2ePro/Ebay_Item')->getMainTable(),
                    array(
                        '`item_id` = ?' => $this->getItemId(),
                        '`product_id` = ?' => $this->getParentObject()->getProductId(),
                        '`account_id` = ?' => $this->getAccount()->getId()
                    ));
    }

    //########################################
}