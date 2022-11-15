<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Listing getParentObject()
 * @method Ess_M2ePro_Model_Resource_Walmart_Listing getResource()
 */
class Ess_M2ePro_Model_Walmart_Listing extends Ess_M2ePro_Model_Component_Child_Walmart_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Template_Description
     */
    protected $_descriptionTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Template_SellingFormat
     */
    protected $_sellingFormatTemplateModel = null;

    /**
     * @var Ess_M2ePro_Model_Template_Synchronization
     */
    protected $_synchronizationTemplateModel = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Walmart_Listing');
    }

    //########################################

    public function deleteInstance()
    {
        $temp = parent::deleteInstance();
        $temp && $this->_descriptionTemplateModel = null;
        $temp && $this->_sellingFormatTemplateModel = null;
        $temp && $this->_synchronizationTemplateModel = null;
        return $temp;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Magento_Product $magentoProduct
     * @return Ess_M2ePro_Model_Walmart_Listing_Source
     */
    public function getSource(Ess_M2ePro_Model_Magento_Product $magentoProduct)
    {
        $productId = $magentoProduct->getProductId();

        if (!empty($this->listingSourceModels[$productId])) {
            return $this->listingSourceModels[$productId];
        }

        $this->listingSourceModels[$productId] = Mage::getModel('M2ePro/Walmart_Listing_Source');
        $this->listingSourceModels[$productId]->setMagentoProduct($magentoProduct);
        $this->listingSourceModels[$productId]->setListing($this->getParentObject());

        return $this->listingSourceModels[$productId];
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
     * @return Ess_M2ePro_Model_Template_Description
     */
    public function getDescriptionTemplate()
    {
        if ($this->_descriptionTemplateModel === null) {
            $this->_descriptionTemplateModel = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
                'Template_Description', $this->getData('template_description_id'), null, array('template')
            );
        }

        return $this->_descriptionTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_Description $instance
     */
    public function setDescriptionTemplate(Ess_M2ePro_Model_Template_Description $instance)
    {
        $this->_descriptionTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_SellingFormat
     */
    public function getSellingFormatTemplate()
    {
        if ($this->_sellingFormatTemplateModel === null) {
            $this->_sellingFormatTemplateModel = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
                'Template_SellingFormat', $this->getData('template_selling_format_id'), null, array('template')
            );
        }

        return $this->_sellingFormatTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_SellingFormat $instance
     */
    public function setSellingFormatTemplate(Ess_M2ePro_Model_Template_SellingFormat $instance)
    {
         $this->_sellingFormatTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Template_Synchronization
     */
    public function getSynchronizationTemplate()
    {
        if ($this->_synchronizationTemplateModel === null) {
            $this->_synchronizationTemplateModel = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
                'Template_Synchronization', $this->getData('template_synchronization_id'), null, array('template')
            );
        }

        return $this->_synchronizationTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Template_Synchronization $instance
     */
    public function setSynchronizationTemplate(Ess_M2ePro_Model_Template_Synchronization $instance)
    {
         $this->_synchronizationTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Walmart_Template_SellingFormat
     */
    public function getWalmartDescriptionTemplate()
    {
        return $this->getDescriptionTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Template_SellingFormat
     */
    public function getWalmartSellingFormatTemplate()
    {
        return $this->getSellingFormatTemplate()->getChildObject();
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Template_Synchronization
     */
    public function getWalmartSynchronizationTemplate()
    {
        return $this->getSynchronizationTemplate()->getChildObject();
    }

    //########################################

    /**
     * @param bool $asObjects
     * @param array $filters
     * @return array
     */
    public function getProducts($asObjects = false, array $filters = array())
    {
        return $this->getParentObject()->getProducts($asObjects, $filters);
    }

    //########################################

    /**
     * @return int
     */
    public function getAutoGlobalAddingCategoryTemplateId()
    {
        return (int)$this->getData('auto_global_adding_category_template_id');
    }

    /**
     * @return int
     */
    public function getAutoWebsiteAddingCategoryTemplateId()
    {
        return (int)$this->getData('auto_website_adding_category_template_id');
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Other $listingOtherProduct
     * @param int $initiator
     * @return bool|Ess_M2ePro_Model_Listing_Product
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function addProductFromOther(
        Ess_M2ePro_Model_Listing_Other $listingOtherProduct,
        $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN
    ) {
        if (!$listingOtherProduct->getProductId()) {
            return false;
        }

        $productId = $listingOtherProduct->getProductId();
        $result = $this->getParentObject()->addProduct($productId, $initiator, false, true);

        if (!($result instanceof Ess_M2ePro_Model_Listing_Product)) {
            return false;
        }

        $listingProduct = $result;

        /** @var Ess_M2ePro_Model_Walmart_Listing_Product $walmartListingProduct */
        $walmartListingProduct = $listingProduct->getChildObject();
        $variationManager = $walmartListingProduct->getVariationManager();

        if ($variationManager->isRelationParentType()) {
            $variationManager->switchModeToAnother();
        }

        /** @var Ess_M2ePro_Model_Walmart_Listing_Other $walmartListingOther */
        $walmartListingOther = $listingOtherProduct->getChildObject();

        $dataForUpdate = array(
            'sku'                     => $walmartListingOther->getSku(),
            'gtin'                    => $walmartListingOther->getGtin(),
            'upc'                     => $walmartListingOther->getUpc(),
            'ean'                     => $walmartListingOther->getEan(),
            'wpid'                    => $walmartListingOther->getWpid(),

            'item_id'                 => $walmartListingOther->getItemId(),

            'online_price'            => $walmartListingOther->getOnlinePrice(),
            'online_qty'              => $walmartListingOther->getOnlineQty(),
            'is_online_price_invalid' => $walmartListingOther->isOnlinePriceInvalid(),

            'status'                  => $listingOtherProduct->getStatus(),
            'status_changer'          => $listingOtherProduct->getStatusChanger(),

            'publish_status'          => $walmartListingOther->getPublishStatus(),
            'lifecycle_status'        => $walmartListingOther->getLifecycleStatus(),

            'status_change_reasons'   => $walmartListingOther->getData('status_change_reasons'),
        );

        $listingProduct->setSetting(
            'additional_data', $listingProduct::MOVING_LISTING_OTHER_SOURCE_KEY, $listingOtherProduct->getId()
        );
        if ($listingProduct->getMagentoProduct()->isGroupedType() &&
            Mage::helper('M2ePro/Module_Configuration')->isGroupedProductModeSet()
        ) {
            $listingProduct->setSetting('additional_data', 'grouped_product_mode', 1);
        }

        $listingProduct->addData($dataForUpdate);
        $listingProduct->save();

        $listingOtherProduct->setSetting(
            'additional_data', $listingOtherProduct::MOVING_LISTING_PRODUCT_DESTINATION_KEY, $listingProduct->getId()
        );
        $listingOtherProduct->save();

        $walmartItem = $walmartListingProduct->getWalmartItem();
        if ($listingProduct->getMagentoProduct()->isGroupedType() &&
            Mage::helper('M2ePro/Module_Configuration')->isGroupedProductModeSet()
        ) {
            $walmartItem->setAdditionalData(json_encode(array('grouped_product_mode' => 1)));
        }

        $walmartItem->setData('store_id', $this->getParentObject()->getStoreId())
            ->save();

        $instruction = Mage::getModel('M2ePro/Listing_Product_Instruction');
        $instruction->setData(
            array(
            'listing_product_id' => $listingProduct->getId(),
            'component'          => Ess_M2ePro_Helper_Component_Walmart::NICK,
            'type'               => Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            'initiator'          => Ess_M2ePro_Model_Listing::INSTRUCTION_INITIATOR_MOVING_PRODUCT_FROM_OTHER,
            'priority'           => 20,
            )
        );
        $instruction->save();

        return $listingProduct;
    }

    public function addProductFromListing(
        Ess_M2ePro_Model_Listing_Product $listingProduct,
        Ess_M2ePro_Model_Listing $sourceListing
    ) {
        if (!$this->getParentObject()->addProductFromListing($listingProduct, $sourceListing, false)) {
            return false;
        }

        if ($this->getParentObject()->getStoreId() != $sourceListing->getStoreId()) {
            if (!$listingProduct->isNotListed()) {
                if ($item = $listingProduct->getChildObject()->getWalmartItem()) {
                    $item->setData('store_id', $this->getParentObject()->getStoreId());
                    $item->save();
                }
            }
        }

        $walmartListingProduct = $listingProduct->getChildObject();
        $variationManager = $walmartListingProduct->getVariationManager();

        if ($variationManager->isRelationParentType()) {
            /** @var Ess_M2ePro_Model_Resource_Walmart_Listing_Product $resourceModel */
            $resourceModel = Mage::getResourceModel('M2ePro/Walmart_Listing_Product');
            $resourceModel->moveChildrenToListing($listingProduct);
        }

        return true;
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('listing');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('listing');
        return parent::delete();
    }

    //########################################
}
