<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Listing_Auto_Category_Group getParentObject()
 */
class Ess_M2ePro_Model_Ebay_Listing_Auto_Category_Group extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    /**
     * @var Ess_M2ePro_Model_Ebay_Template_Category
     */
    private $categoryTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    private $otherCategoryTemplateModel = NULL;

    /**
     * @var Ess_M2ePro_Model_Magento_Product
     */
    private $magentoProductModel = NULL;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Listing_Auto_Category_Group');
    }

    //########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        $this->categoryTemplateModel = NULL;
        $this->otherCategoryTemplateModel = NULL;
        $this->magentoProductModel = NULL;

        $this->delete();
        return true;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_Category
     */
    public function getCategoryTemplate()
    {
        if (is_null($this->categoryTemplateModel)) {

            try {
                $this->categoryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                    'Ebay_Template_Category', (int)$this->getAddingTemplateCategoryId(), NULL, array('template')
                );
            } catch (Exception $exception) {
                return $this->categoryTemplateModel;
            }

            if (!is_null($this->getMagentoProduct())) {
                $this->categoryTemplateModel->setMagentoProduct($this->getMagentoProduct());
            }
        }

        return $this->categoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_Category $instance
     */
    public function setCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_Category $instance)
    {
        $this->categoryTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Ebay_Template_OtherCategory
     */
    public function getOtherCategoryTemplate()
    {
        if (is_null($this->otherCategoryTemplateModel)) {

            try {
                $this->otherCategoryTemplateModel = Mage::helper('M2ePro')->getCachedObject(
                    'Ebay_Template_OtherCategory', (int)$this->getAddingTemplateOtherCategoryId(),
                    NULL, array('template')
                );
            } catch (Exception $exception) {
                return $this->otherCategoryTemplateModel;
            }

            if (!is_null($this->getMagentoProduct())) {
                $this->otherCategoryTemplateModel->setMagentoProduct($this->getMagentoProduct());
            }
        }

        return $this->otherCategoryTemplateModel;
    }

    /**
     * @param Ess_M2ePro_Model_Ebay_Template_OtherCategory $instance
     */
    public function setOtherCategoryTemplate(Ess_M2ePro_Model_Ebay_Template_OtherCategory $instance)
    {
        $this->otherCategoryTemplateModel = $instance;
    }

    // ---------------------------------------

    /**
     * @return Ess_M2ePro_Model_Magento_Product
     */
    public function getMagentoProduct()
    {
        return $this->magentoProductModel;
    }

    /**
     * @param Ess_M2ePro_Model_Magento_Product $instance
     */
    public function setMagentoProduct(Ess_M2ePro_Model_Magento_Product $instance)
    {
        $this->magentoProductModel = $instance;
    }

    //########################################

    public function getAddingTemplateCategoryId()
    {
        return $this->getData('adding_template_category_id');
    }

    public function getAddingTemplateOtherCategoryId()
    {
        return $this->getData('adding_template_other_category_id');
    }

    //########################################

    /**
     * @return bool
     */
    public function isAddingModeAddAndAssignCategory()
    {
        return $this->getParentObject()->getAddingMode() ==
               Ess_M2ePro_Model_Ebay_Listing::ADDING_MODE_ADD_AND_ASSIGN_CATEGORY;
    }

    //########################################
}