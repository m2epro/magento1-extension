<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor
{
    //########################################

    /** @var Ess_M2ePro_Model_Listing_Product $_listingProduct */
    protected $_listingProduct = null;

    protected $_marketplaceId = null;

    /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent $_typeModel */
    protected $_typeModel = null;

    /** @var Ess_M2ePro_Model_Template_Description $_descriptionTemplate */
    protected $_descriptionTemplate = null;

    /** @var Ess_M2ePro_Model_Walmart_Template_Category $descriptionTemplate */
    protected $_walmartCategoryTemplate = null;

    protected $_possibleChannelAttributes = null;

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        return $this->_listingProduct;
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Product
     */
    public function getWalmartListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    /**
     * @param $listingProduct
     * @return $this
     */
    public function setListingProduct($listingProduct)
    {
        $this->_listingProduct = $listingProduct;
        return $this;
    }

    //########################################

    public function process()
    {
        if ($this->_listingProduct === null) {
            throw new Ess_M2ePro_Model_Exception('Listing Product was not set.');
        }

        $this->getTypeModel()->enableCache();

        foreach ($this->getSortedProcessors() as $processor) {
            $this->getProcessorModel($processor)->process();
        }

        $this->_listingProduct->setData('variation_parent_need_processor', 0);

        $this->_listingProduct->save();
    }

    //########################################

    protected function getSortedProcessors()
    {
        return array(
            'Template',
            'Attributes',
            'MatchedAttributes',
            'Options',
            'Status',
            'Selling',
        );
    }

    /**
     * @param  string $processorName
     * @return Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Abstract
     */
    protected function getProcessorModel($processorName)
    {
        $model = Mage::getModel(
            'M2ePro/Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_'.$processorName
        );
        $model->setProcessor($this);

        return $model;
    }

    //########################################

    /**
     * @return array
     */
    public function getMagentoProductVariations()
    {
        return $this->getListingProduct()
            ->getMagentoProduct()
            ->getVariationInstance()
            ->getVariationsTypeStandard();
    }

    public function getProductVariation(array $options)
    {
        return $this->getListingProduct()
            ->getMagentoProduct()
            ->getVariationInstance()
            ->getVariationTypeStandard($options);
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent
     */
    public function getTypeModel()
    {
        if ($this->_typeModel !== null) {
            return $this->_typeModel;
        }

        return $this->_typeModel = $this->getWalmartListingProduct()
                                        ->getVariationManager()
                                        ->getTypeModel();
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $childListingProduct
     * @return bool
     */
    public function tryToRemoveChildListingProduct(Ess_M2ePro_Model_Listing_Product $childListingProduct)
    {
        if ($childListingProduct->isLocked()) {
            return false;
        }

        if ($childListingProduct->isStoppable()) {
            Mage::getModel('M2ePro/StopQueue')->add($childListingProduct);
        }

        $this->getTypeModel()->removeChildListingProduct($childListingProduct->getId());

        return true;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Template_Description
     */
    public function getDescriptionTemplate()
    {
        if ($this->_descriptionTemplate !== null) {
            return $this->_descriptionTemplate;
        }

        return $this->_descriptionTemplate = $this->getWalmartListingProduct()->getDescriptionTemplate();
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
     * @return Ess_M2ePro_Model_Walmart_Template_Category
     */
    public function getWalmartCategoryTemplate()
    {
        if ($this->_walmartCategoryTemplate !== null) {
            return $this->_walmartCategoryTemplate;
        }

        return $this->_walmartCategoryTemplate = $this->getWalmartListingProduct()->getCategoryTemplate();
    }

    //########################################

    /**
     * @return array|null
     */
    public function getPossibleChannelAttributes()
    {
        if ($this->_possibleChannelAttributes !== null) {
            return $this->_possibleChannelAttributes;
        }

        $possibleChannelAttributes = Mage::getModel('M2ePro/Walmart_Marketplace_Details')
            ->setMarketplaceId($this->getMarketplaceId())
            ->getVariationAttributes(
                $this->getWalmartCategoryTemplate()->getProductDataNick()
            );

        return $this->_possibleChannelAttributes = $possibleChannelAttributes;
    }

    /**
     * @return int|null
     */
    public function getMarketplaceId()
    {
        if ($this->_marketplaceId !== null) {
            return $this->_marketplaceId;
        }

        return $this->_marketplaceId = $this->getListingProduct()->getListing()->getMarketplaceId();
    }

    //########################################
}
