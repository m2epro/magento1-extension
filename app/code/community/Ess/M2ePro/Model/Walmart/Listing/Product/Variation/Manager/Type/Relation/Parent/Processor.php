<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor
{
    //########################################

    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    private $listingProduct = null;

    private $marketplaceId = null;

    /** @var Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent $typeModel */
    private $typeModel = null;

    /** @var Ess_M2ePro_Model_Template_Description $descriptionTemplate */
    private $descriptionTemplate = null;

    /** @var Ess_M2ePro_Model_Walmart_Template_Category $descriptionTemplate */
    private $walmartCategoryTemplate = null;

    private $possibleChannelAttributes = null;

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        return $this->listingProduct;
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
        $this->listingProduct = $listingProduct;
        return $this;
    }

    //########################################

    public function process()
    {
        if (is_null($this->listingProduct)) {
            throw new Ess_M2ePro_Model_Exception('Listing Product was not set.');
        }

        $this->getTypeModel()->enableCache();

        foreach ($this->getSortedProcessors() as $processor) {
            $this->getProcessorModel($processor)->process();
        }

        $this->listingProduct->setData('variation_parent_need_processor', 0);

        $this->listingProduct->save();
    }

    //########################################

    private function getSortedProcessors()
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
    private function getProcessorModel($processorName)
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
        if (!is_null($this->typeModel)) {
            return $this->typeModel;
        }

        return $this->typeModel = $this->getWalmartListingProduct()
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
        if (!is_null($this->descriptionTemplate)) {
            return $this->descriptionTemplate;
        }

        return $this->descriptionTemplate = $this->getWalmartListingProduct()->getDescriptionTemplate();
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
        if (!is_null($this->walmartCategoryTemplate)) {
            return $this->walmartCategoryTemplate;
        }

        return $this->walmartCategoryTemplate = $this->getWalmartListingProduct()->getCategoryTemplate();
    }

    //########################################

    /**
     * @return array|null
     */
    public function getPossibleChannelAttributes()
    {
        if (!is_null($this->possibleChannelAttributes)) {
            return $this->possibleChannelAttributes;
        }

        $possibleChannelAttributes = Mage::getModel('M2ePro/Walmart_Marketplace_Details')
            ->setMarketplaceId($this->getMarketplaceId())
            ->getVariationAttributes(
                $this->getWalmartCategoryTemplate()->getProductDataNick()
            );

        return $this->possibleChannelAttributes = $possibleChannelAttributes;
    }

    /**
     * @return int|null
     */
    public function getMarketplaceId()
    {
        if (!is_null($this->marketplaceId)) {
            return $this->marketplaceId;
        }

        return $this->marketplaceId = $this->getListingProduct()->getListing()->getMarketplaceId();
    }

    //########################################
}