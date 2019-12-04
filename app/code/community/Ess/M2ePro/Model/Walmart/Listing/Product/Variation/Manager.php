<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager
{
    /**
     * @var Ess_M2ePro_Model_Listing_Product
     */
    protected $_listingProduct = null;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     */
    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->_listingProduct = $listingProduct;
    }

    // ---------------------------------------

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

    //########################################

    /**
     * @return bool
     */
    public function isVariationProduct()
    {
        return (bool)(int)$this->getWalmartListingProduct()->getData('is_variation_product');
    }

    /**
     * @return bool
     */
    public function isVariationParent()
    {
        return (bool)(int)$this->getWalmartListingProduct()->getData('is_variation_parent');
    }

    /**
     * @return int
     */
    public function getVariationParentId()
    {
        return (int)$this->getWalmartListingProduct()->getData('variation_parent_id');
    }

    //########################################

    /**
     * @return bool
     */
    public function isSimpleType()
    {
        return !$this->isVariationProduct();
    }

    /**
     * @return bool
     */
    public function isIndividualType()
    {
        return $this->isVariationProduct() &&
               !$this->isVariationParent() &&
               !$this->getVariationParentId();
    }

    /**
     * @return bool
     */
    public function isRelationParentType()
    {
        return $this->isVariationProduct() &&
               $this->isVariationParent() &&
               !$this->getVariationParentId();
    }

    /**
     * @return bool
     */
    public function isRelationChildType()
    {
        return $this->isVariationProduct() &&
               !$this->isVariationParent() &&
               $this->getVariationParentId();
    }

    // ---------------------------------------

    public function setSimpleType()
    {
        $this->getListingProduct()->setData('is_variation_product', 0)
                                  ->setData('is_variation_parent', 0)
                                  ->setData('variation_parent_id', null)
                                  ->save();
    }

    public function setIndividualType()
    {
        $this->getListingProduct()->setData('is_variation_parent', 0)
                                  ->setData('variation_parent_id', null)
                                  ->save();
    }

    public function setRelationParentType()
    {
        $this->getListingProduct()->setData('is_variation_parent', 1)
                                  ->setData('variation_parent_id', null)
                                  ->save();
    }

    public function setRelationChildType($variationParentId)
    {
        $this->getListingProduct()->setData('is_variation_parent', 0)
                                  ->setData('variation_parent_id', $variationParentId)
                                  ->save();
    }

    //########################################

    /**
     * @return bool
     */
    public function isIndividualMode()
    {
        return $this->isIndividualType();
    }

    /**
     * @return bool
     */
    public function isRelationMode()
    {
        return $this->isRelationParentType() || $this->isRelationChildType();
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isLogicalUnit()
    {
        return $this->isRelationParentType();
    }

    /**
     * @return bool
     */
    public function isPhysicalUnit()
    {
        return $this->isIndividualType() || $this->isRelationChildType();
    }

    //########################################

    /**
     * @return mixed
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getTypeModel()
    {
        $model = null;

        if ($this->isIndividualType()) {
            $model = Mage::getModel('M2ePro/Walmart_Listing_Product_Variation_Manager_Type_Individual');
        } else if ($this->isRelationParentType()) {
            $model = Mage::getModel('M2ePro/Walmart_Listing_Product_Variation_Manager_Type_Relation_Parent');
        } else if ($this->isRelationChildType()) {
            $model = Mage::getModel('M2ePro/Walmart_Listing_Product_Variation_Manager_Type_Relation_Child');
        } else {
            throw new Ess_M2ePro_Model_Exception('This Product is not a Variation Product.');
        }

        /** @var $model Ess_M2ePro_Model_Walmart_Listing_Product_Variation_Manager_Abstract */
        $model->setVariationManager($this);

        return $model;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function modeCanBeSwitched()
    {
        return $this->isIndividualType() || $this->isRelationParentType();
    }

    public function switchModeToAnother()
    {
        if (!$this->modeCanBeSwitched()) {
            return false;
        }

        if ($this->isIndividualType()) {
            $this->getTypeModel()->clearTypeData();
            $this->setRelationParentType();
            $this->getTypeModel()->resetProductAttributes();
            $this->getTypeModel()->getProcessor()->process();
        } else if ($this->isRelationParentType()) {
            $this->getTypeModel()->getProcessor()->process();
            $this->getTypeModel()->clearTypeData();
            $this->setIndividualType();
            $this->getTypeModel()->resetProductVariation();
        }

        return true;
    }

    //########################################
}
