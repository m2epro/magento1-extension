<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor
{
    //########################################

    /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
    private $listingProduct = null;

    private $marketplaceId = null;

    /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent $typeModel */
    private $typeModel = null;

    /** @var Ess_M2ePro_Model_Amazon_Template_Description $descriptionTemplate */
    private $descriptionTemplate = null;

    private $possibleThemes = null;

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        return $this->listingProduct;
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing_Product
     */
    public function getAmazonListingProduct()
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
            'GeneralIdOwner',
            'Attributes',
            'Theme',
            'MatchedAttributes',
            'Options',
            'Status',
            'Selling',
        );
    }

    /**
     * @param  string $processorName
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_Abstract
     */
    private function getProcessorModel($processorName)
    {
        $model = Mage::getModel(
            'M2ePro/Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor_Sub_'.$processorName
        );
        $model->setProcessor($this);

        return $model;
    }

    //########################################

    /**
     * @return bool
     */
    public function isGeneralIdSet()
    {
        return (bool)$this->getAmazonListingProduct()->getGeneralId();
    }

    /**
     * @return bool
     */
    public function isGeneralIdOwner()
    {
        return $this->getAmazonListingProduct()->isGeneralIdOwner();
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
     * @return Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent
     */
    public function getTypeModel()
    {
        if (!is_null($this->typeModel)) {
            return $this->typeModel;
        }

        return $this->typeModel = $this->getAmazonListingProduct()
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

        return $this->descriptionTemplate = $this->getAmazonListingProduct()->getDescriptionTemplate();
    }

    /**
     * @return Ess_M2ePro_Model_Amazon_Template_Description
     */
    public function getAmazonDescriptionTemplate()
    {
        return $this->getDescriptionTemplate()->getChildObject();
    }

    //########################################

    /**
     * @return array|null
     */
    public function getPossibleThemes()
    {
        if (!is_null($this->possibleThemes)) {
            return $this->possibleThemes;
        }

        $marketPlaceId = $this->getMarketplaceId();

        $possibleThemes = Mage::getModel('M2ePro/Amazon_Marketplace_Details')
            ->setMarketplaceId($marketPlaceId)
            ->getVariationThemes(
                $this->getAmazonDescriptionTemplate()->getProductDataNick()
            );

        $variationHelper = Mage::helper('M2ePro/Component_Amazon_Variation');
        $themesUsageData = $variationHelper->getThemesUsageData();
        $usedThemes = array();

        if (!empty($themesUsageData[$marketPlaceId])) {
            foreach ($themesUsageData[$marketPlaceId] as $theme => $count) {
                if (!empty($possibleThemes[$theme])) {
                    $usedThemes[$theme] = $possibleThemes[$theme];
                }
            }
        }

        return $this->possibleThemes = array_merge($usedThemes, $possibleThemes);
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