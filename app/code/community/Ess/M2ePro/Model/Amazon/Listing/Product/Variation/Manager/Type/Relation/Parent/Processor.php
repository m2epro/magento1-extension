<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent_Processor
{
    /** @var Ess_M2ePro_Model_Listing_Product $_listingProduct */
    protected $_listingProduct = null;

    protected $_marketplaceId = null;

    /** @var Ess_M2ePro_Model_Amazon_Listing_Product_Variation_Manager_Type_Relation_Parent $_typeModel */
    protected $_typeModel = null;

    /** @var Ess_M2ePro_Model_Template_Description $_descriptionTemplate */
    protected $_descriptionTemplate = null;

    protected $_possibleThemes = null;

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing_Product
     */
    public function getListingProduct()
    {
        return $this->_listingProduct;
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
        $this->_listingProduct = $listingProduct;
        return $this;
    }

    //########################################

    /**
     * @throws Ess_M2ePro_Model_Exception
     */
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
    protected function getProcessorModel($processorName)
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
        if ($this->_typeModel !== null) {
            return $this->_typeModel;
        }

        return $this->_typeModel = $this->getAmazonListingProduct()
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

        return $this->_descriptionTemplate = $this->getAmazonListingProduct()->getDescriptionTemplate();
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
        if ($this->_possibleThemes !== null) {
            return $this->_possibleThemes;
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

        return $this->_possibleThemes = array_merge($usedThemes, $possibleThemes);
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
